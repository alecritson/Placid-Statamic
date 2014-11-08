# Placid Beta (Statamic)
~~This is a port of my Placid plugin that I built for Craft.It doesn’t have all the same features yet but you can consume any **open** API in your Statamic templates~~

Placid allows you to consume RESTful APIs in your Statamic templates, using Guzzle to handle the requests. 

Placid (Statamic) supports:
 - Caching/Refreshing
 - Template variable pairs
 - Predefined requests
 - Headers
 - Access tokens

 #### Updates / Changes
 - **Version 0.8.0** - Added support for headers to be sent from the config
 - **Version 0.7.0** - Added support for access tokens in the config
 - **Version 0.6.0** - Guzzle is now being used instead of cURL
 - **Version 0.5.7** - Fixed issue where cache would take effect even if set to false in config
 - **Version 0.5.5** - Initial release
	
### Installation
Copy the placid folder to your **_add-ons** directory and you're good to go

### Parameters
- **URL**: The URL to request
- **refresh** (number): The time in seconds until the cache refreshes (default is 7200 / 2 hours)
- **handle** (string) : The handle specified in the placid config
- **cache** (boolean) : Whether you want the request to be cached (default is true)
- **method** (string) : You can set which method to use on the request, default is 'GET' 

### Saved requests
You can set up requests for placid in **_config/add-ons/placid.yaml** like so:

	dribbble:
		url: 'http://api.dribbble.com/shots/everyone'
		cache: true
		refresh: 60

	weather_api:
		url: 'http://api.openweathermap.org/data/2.5/weather'
		query:
			q: 'London,uk'

	github:
		url: 'https://api.github.com/repos/alecritson/Placid-Statamic'
		access_token: OAUTH-TOKEN
		headers:
			Authorization: token OAUTH-TOKEN

*The query array works out as ‘q=London,uk’ in the url*

**If you use `access_token` it will be appended to the url, if you use the `headers` array then it will be sent through the request headers.**
## Usage

To use this plugin in your templates, simply use these tags:

### Example Code Block with manual URL
 
	{{ placid url=“http://api.dribbble.com/shots/everyone” }}
		{{ shots }}
		 {{ title }}
		{{ /shots }}
	{{ /placid }}

### Example code block with handle
	{{ placid handle=“dribbble” }}
		{{ shots }}
		 {{ title }}
		{{ /shots }}
	{{ /placid }}

*If you are unsure as to what tags to use within the placid variable pair, just pop the api url into your browser and work it out from there*

### Handling no results
You can catch when there are no results just like you would in an entries loop:

	{{ placid url=“http://www.dustysquirrels.com/noapi” }}
		{{ if no_results }}
			No results
		{{ else }}
			Squirrels!
		{{ endif }}
	{{ /placid }}

## Support,issues,feedback
If you want to leave feedback about this project, feel free to get in touch on [twitter](http://www.twitter.com/alecritson) if you experience any issues please just create a new issue here on the Repo