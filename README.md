# current-pivotal-work
Displays the ticket title and number of the tickets that I am currently working on

# Setup and starting
1. Clone this project
1. `cd current-pivotal-work`
1. Update .env file.  See the [Environment variables section](#environment-variables)
1. ***DO NOT RUN THE NEXT STEP WITHOUT FILLING IN THE REQUIRED ENV VARIABLES***
1. `docker-compose up -d`
1. Navigate to [http://localhost:9876](http://localhost:9876)
  * If you changed the default port then replace 9876 with your custom port value

## Environmental variables
Environment variables can be found in the `.env` file in the root of this repository.

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| PIVOTAL_TRACKER_API_KEY | string | `` | This is your Pivotal Tracker API Key.  See the Pivotal Tracker section for instructions on finding this key. |
| PIVOTAL_TRACKER_OWNER | string | `` | Your Pivotal Tracker initials.  This will be used to identify your work. |
| PORT | int | `9876` | The port on your host machine that you want to use to access the interface. |
| APP_ENV | string | `dev` | The mode that the application will run in. |
| DATABASE_NAME | string | `current_pivotal_work` | The name of the database that will be created/used. |
| DATABASE_USER | string | `current_pivotal_work` | The username that will be created/used to connect to the database. |
| DATABASE_PASSWORD | string | ` ` | The password for the `DATABASE_USER`.  This needs to be filled in with a strong password before running application. |
| DATABASE_ROOT_PASSWORD | string | ` ` | The password for the `DATABASE_ROOT_USER`.  This needs to be filled in with a strong password before running application. |


## Pivotal Tracker
This section will show you how to find your Pivotal Tracker information

### Pivotal Tracker Owner
1. Open up and log into [Pivotal Tracker](https://www.pivotaltracker.com/)
1. Once logged in you should be able to navigate to your [Pivotal Tracker Profile](https://www.pivotaltracker.com/profile)
1. In the `My Profile` section you will find your `Initials`.  Copy this value into `PIVOTAL_TRACKER_OWNER` in the `.env` file

### Pivotal Tracker API Token
1. Open up and log into [Pivotal Tracker](https://www.pivotaltracker.com/)
1. Once logged in you should be able to navigate to your [Pivotal Tracker Profile](https://www.pivotaltracker.com/profile)
1. In the `API Token` section you will find your `API token`.  If a token is not present in this section then click on the `Create New Token` button.  Once the API token is created you can copy this value into `PIVOTAL_TRACKER_API_KEY` in the `.env` file