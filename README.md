# current-pivotal-work
Displays the ticket title and number of the tickets that I am currently working on

1. Checkout this project
1. `cd current-pivotal-work`
1. Download the [`composer.phar`](https://getcomposer.org/composer.phar) executable or use the installer.

    ``` sh
    $ curl -sS https://getcomposer.org/installer | php
    ```
1. Run Composer: `php composer.phar install`
1. `cd web`
1. `php -S localhost:9999`
1. Navigate to [http://localhost:9999/key/your_pivotal_api_key/project/your_pivotal_project_id/owner/your_initials](http://localhost:9999/key/your_pivotal_api_key/project/your_pivotal_project_id/owner/your_initials)
1. This page will update once a minute to stay current
1. Clicking on the pivotal tracker number should automatically copy the number to your clipboard
