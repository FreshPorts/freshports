.PHONY:	all clean
default: fwatch

fwatch: fwatch.lhs
	$(HC) -syslib posix -syslib util -syslib concurrent $< -o $@
	strip $@

clean:
	rm -rf Main.hi fwatch.o *~