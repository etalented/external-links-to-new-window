# 	External Links to New Window

This is a plugin for WordPress which, when enabled, will open all links in a new window or tab that are not of the same domain as the WordPress site. It works only on blog post and page content as it makes use of the WordPress [`the_content` filter](https://codex.wordpress.org/Plugin_API/Filter_Reference/the_content) - this is a core feature of the plugin as it is designed to be as lean and least-cumbersome as possible.

## Running the tests

1. [Install PHPUnit](https://phpunit.de/manual/6.5/en/installation.html)
1. [Install WP-CLI](https://wp-cli.org/)
1. Run the command `bin/install-wp-tests.sh wordpress_test username 'password' localhost latest` from the plugin root. `wordpress_test` is the name of the MySQL database that will be created. `username` the name for the MySQL connection. `password` the password for the MySQL connection. `localhost` the host of the MySQL database.
1. Run the command `phpunit` to run all the tests

More help if you need it thanks to [Smashing Magazine](https://www.smashingmagazine.com/2017/12/automated-testing-wordpress-plugins-phpunit/).

## Built with

* [PHPUnit](https://phpunit.de/)
* [PHP Simple HTML DOM Parser](http://sourceforge.net/projects/simplehtmldom/)

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests.

## Authors

* Authored by [Etalented](https://etalented.co.uk)
* Initial work by [Christopher Ross](http://thisismyurl.com)

## License

This software is licensed under the GNU GENERAL PUBLIC LICENSE - see the LICENSE.md file for details.