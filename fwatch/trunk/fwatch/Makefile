.PHONY:	all
default: fwatch

fwatch: fwatch.lhs
	ghc -syslib posix -syslib util -syslib concurrent $< -o $@
	strip $@
