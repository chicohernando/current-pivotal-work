# current-pivotal-work
Displays the ticket title and number of the tickets that I am currently working on

# Setup and starting
1. Clone this project
1. `cd current-pivotal-work`
1. Run Composer: `php composer.phar install`
1. Update .env file.  See the [Environment variables section](#environment-variables)
1. `docker-compose up -d`
1. Navigate to [http://localhost:9876](http://localhost:9876)
  * If you changed the default port then replace 9876 with your custom port value

## Environmental variables
Environment variables can be found in the `.env` file in the root of this repository.

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| PIVOTAL_TRACKER_API_KEY | string | `` | This is your Pivotal Tracker API Key.  See the Pivotal Tracker section for instructions on finding this key. |
| PIVOTAL_TRACKER_PROJECT_ID | int | `` | The id of the Pivotal Tracker Project that you want data for. |
| PIVOTAL_TRACKER_OWNER | string | `` | Your Pivotal Tracker initials.  This will be used to identify your work. |
| PIVOTAL_TRACKER_TEAM_INITIALS | string | `` | A comma separated list of the initials of people that you want to see data for. Do not put spaces after the commas. |
| PORT | int | `9876` | The port on your host machine that you want to use to access the interface. |
| REPOSITORY_PATH | string | `.` | The path to this repository on your local machine.  This is useful if you want to be able to start the container from any directory. |


## Pivotal Tracker
This section will show you how to find your Pivotal Tracker information

### Pivotal Tracker Initials
1. Open up and log into [Pivotal Tracker](https://www.pivotaltracker.com/)
1. Once logged in you should be able to navigate to your [Pivotal Tracker Profile](https://www.pivotaltracker.com/profile)
1. In the `My Profile` section you will find your `Initials`.  Copy this value into `PIVOTAL_TRACKER_OWNER` in the `.env` file

### Pivotal Tracker API Token
1. Open up and log into [Pivotal Tracker](https://www.pivotaltracker.com/)
1. Once logged in you should be able to navigate to your [Pivotal Tracker Profile](https://www.pivotaltracker.com/profile)
1. In the `API Token` section you will find your `API token`.  If a token is not present in this section then click on the `Create New Token` button.  Once the API token is created you can copy this value into `PIVOTAL_TRACKER_API_KEY` in the `.env` file

### Pivotal Tracker Project ID
1. Open up and log into [Pivotal Tracker](https://www.pivotaltracker.com/)
1. Once logged in you should be able to navigate to your [Pivotal Tracker Dashboard](https://www.pivotaltracker.com/dashboard)
1. Click on the Project that you want information for
1. You should now be on a page with a URL similar to `https://www.pivotaltracker.com/n/projects/123456`
1. Copy the `123456` value (your value with be different) into `PIVOTAL_TRACKER_PROJECT_ID` in your `.env` file

### Pivotal Tracker Team Initials
1. Open up and log into [Pivotal Tracker](https://www.pivotaltracker.com/)
1. Once logged in you should be able to navigate to your [Pivotal Tracker Dashboard](https://www.pivotaltracker.com/dashboard)
1. Click on the Project that you want information for
1. Click on the Members tab
1. If the team member has a profile picture you can hover it and in the hover popup you will find their initials
1. If the team member does not have a profile picture you will find their initials to the left of their name in a grey circle
1. Copy these values into `PIVOTAL_TRACKER_TEAM_INITIALS` in the `.env` file