all: unit functional acceptance

generic-test:
	@echo "Running ${type} tests ..."
	@for file in `find ./tests/${type}/ -name '*.php'`; do php -c php.ini $$file; done

unit:
	@make generic-test type=unit

functional:
	@make generic-test type=functional