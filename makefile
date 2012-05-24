PHAR=./kickstarter.phar

all: clean phar

clean:
	rm -rf $(PHAR)
phar:
	phar pack -b '#!/usr/bin/php' -f $(PHAR) -s src/kickstarter.php src
	chmod +x $(PHAR)

