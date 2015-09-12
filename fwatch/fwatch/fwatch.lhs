A simple tool for reading input from a FIFO, queuing up the requests and
invoke a script which does something with the stuff.

Basic input-filtering for shell-escapes is in place. Note that Haskell is
immune against buffer-overflows. But maybe the programs you´re invoking are
not.

Compile using the supplied makefile or with:
  ghc -syslib concurrent -syslib util -syslib posix -o fwatch fwatch.lhs

Example usage:
  ./fwatch --fifo=/tmp/fwatch.in -c"sleep 2; echo " 

ToDo:
  - verbose-switch is a NOP
  - we don´t do any escaping (e.g. blanks) ourselves

http://www.haskell.org
http://www.haskell.org/onlinelibrary/
http://www.haskell.org/ghc/

Volker Stolz <stolz@i2.informatik.rwth-aachen.de>
2000-12-28

\begin{code}
module Main where

import IO
import System
import Concurrent
import GetOpt
import Posix
import Monad
import Maybe

data Opts = Verbose | Fifo String | Cmd String deriving (Eq)
options :: [OptDescr Opts]
options =
  [Option ['v']	["verbose"]	(NoArg Verbose)		"verbose",
   Option ['i']	["fifo"]	(ReqArg Fifo "")	"absolute path to FIFO to read from",
   Option ['c']	["command"]	(ReqArg Cmd "")		"command to execute"]

main :: IO ()
main = do
  (fifoname,command,verbose) <- processArgs
  putStrLn "fwatch starting..."
\end{code}

We need a signal-handler to wait for our children. We let him set a MVar
we can suspend on:

\begin{code}
  sem <- newEmptyMVar
  _ <- installHandler sigCHLD (Catch (putMVar sem ())) Nothing
\end{code}

Prepare the worker process. Create a channel for internal communication
and pass it along with the command from the command-line.

\begin{code}
  ch <- newChan
  forkIO (worker sem command ch)
\end{code}

Open the FIFO, create if necessary. Before we start to read we have to
make sure we obtain a handle for writing, too (which we don´t need, though).
Otherwise we will ge an EOF! Check your local copy of Stevens "Unix Network
Programming" for the gory details.
After setup, enter the main loop.

\begin{code}
  h <- catch (openFile fifoname ReadMode)
	     (\e -> if (isDoesNotExistError e)
		       then do
			 putStrLn $ "Creating FIFO " ++ fifoname
		         _ <- system $ "/usr/bin/mkfifo " ++ fifoname
			 openFile fifoname ReadMode
		       else error $ "Can´t create FIFO " ++ fifoname
              )
  hSetBuffering h LineBuffering
  dummy <- openFile fifoname WriteMode
  fifoReadLoop h ch
\end{code}

\begin{code}
 where
  fifoReadLoop :: Handle -> Chan String -> IO ()
  fifoReadLoop h ch = do
    msg <- hGetLine h
    -- could test for Exit-signal here
    writeChan ch msg
    -- loop
    fifoReadLoop h ch

  worker :: MVar () -> String -> Chan String -> IO ()
  worker sem cmd ch = do
    msg <- readChan ch
    pid <- forkProcess
    case pid of
      Nothing -> do
        -- we inherit the sigmask from our parent
        _ <- installHandler sigCHLD Ignore Nothing
	-- filter strange characters
        let msg' = map (filterF msg) msg
        let cmdWithArg = cmd ++ " " ++ msg'
        child cmdWithArg
        exitWith ExitSuccess
        -- NOT REACHED
      Just p -> do -- parent
        takeMVar sem
	_ <- getProcessStatus False False p
	return ()
    -- loop
    worker sem cmd ch
   where
     -- filterF cl '\\' = charError cl
     filterF cl '`'  = charError cl
     filterF cl '"'  = charError cl
     filterF cl x    = x
     charError cl = error $ "Illegal character in " ++ cl

child cmdWithArg = do
  res <- system cmdWithArg
  case res of
    ExitFailure i -> do
      putStrLn $ "command " ++ cmdWithArg ++ " failed, exit code: " ++ (show i)
    _ -> return ()

processArgs = do
  args <- catch (System.getArgs)
		(\_ -> return [])
  opts <- case (getOpt Permute options args) of
               (o,n,[]  ) -> return o
               (_,_,errs) -> ioError (userError (concat errs ++ usageInfo "Usage: fwatch" options))
  let fifoname = foldl (\n x -> case x of
				  Fifo f -> Just f
				  _      -> n) Nothing opts
  when (isNothing fifoname) (usageError "FIFO name not set!")
  let command = foldl (\n x -> case x of
				  Cmd c -> Just c
				  _     -> n) Nothing opts
  when (isNothing command) (usageError "command not set!")
  let verbose  = Verbose `elem` opts
  return (fromJust fifoname,fromJust command,verbose)

usageError :: String -> IO ()
usageError str = do
  putStrLn str
  putStr (usageInfo "Usage: fwatch" options)
  exitFailure
\end{code}
