LoadUsersFromFP1IntoFP2.pl           - load data into FP2
LoadWatchListFromFP1IntoFP2.pl       - load data into FP2
LoadWatchListPortsFromFP1IntoFP2.pl  - load data into FP2

UsersFromFP1.pl                      - extract data from FP1 for use by LoadUsersFromFP1IntoFP2.pl
WatchListFromFP1.pl                  - extract data from FP1 for use by LoadWatchListFromFP1IntoFP2.pl
WatchListPortsFromFP1.pl             - extract data from FP1 for use by LoadWatchListPortsFromFP1IntoFP2.pl

extract the data from FP1
cat users.txt            | perl UsersFromFP1.pl
delete from watch_list 
cat watch_list.txt       | perl WatchListFromFP1.pl
cat watch_list_ports.txt | perl WatchListPortsFromFP1.pl
