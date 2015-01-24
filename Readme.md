# Placid Beta (Statamic)

Placid allows you to consume RESTful APIs in your Statamic templates, using Guzzle to handle the requests. 

**Placid supports**:
- Caching/Refreshing
- Template variable pairs
- Predefined requests
- Headers
- Access tokens
 
#### Updates / Changes
- **v1.0.0** - Version 1, fixes and enhancements
- **v0.9.2** - Refactoring and added [API](#api) methods
- **v0.9** - Bug fixes, refactoring, added [default](#defaults) config and reusable [access tokens](#access_tokens)
- **v0.8.9** - Bug fixes, refactoring and added [query](#queries) parameter
- **v0.8.7** - Bug fixes
- **v0.8** - Added support for headers to be sent from the config
- **v0.7** - Added support for access tokens in the config
- **v0.6** - Guzzle is now being used instead of cURL
- **v0.5.7** - Fixed issue where cache would take effect even if set to false in config
- **v0.5.5** - Initial release


### Installation
Copy the placid folder to your **_add-ons** directory and you're good to go

### Troubleshooting
If you are not seeing the intended results then set `_log_enabled:` to true in your site `settings.yaml` file (line 125)

### Parameters
- **URL**: The URL to request
- **refresh** (number): The time in seconds until the cache refreshes (default is 7200 / 2 hours)
- **handle** (string) : The handle specified in the placid config
- **cache** (boolean) : Whether you want the request to be cached (default is 1)
- **method** (string) : You can set which method to use on the request, default is 'GET' 
- **query** (string)  : Add your queries here, see [queries](#queries) for more info
- **path** (string) : Add your own custom path, see [paths](#paths) for details

### Saved requests
You can set up requests for placid in **_config/add-ons/placid.yaml** like so:

	dribbble:
		url: 'http://api.dribbble.com/shots/everyone'
		cache: 1
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

The query array works out as `q=London,uk` in the url

**If you use `access_token` it will be appended to the url, if you use the `headers` array then it will be sent through the request headers.**

### Defaults
You can specify default config values for Placid to use

	placid_defaults:
		refresh: 1000
		
#### Access tokens
You can define access tokens in the config so you can use them in your templates without having to re enter them for every request, they look like this in the config:

	placid_tokens:
		github: SOME-ACCESS-TOKEN

## Usage

To use this plugin in your templates, simply use these tags:

### Example Code Block with manual URL
 
	{{ placid url="http://api.dribbble.com/shots/everyone" cache="0" refresh="1200" }}
		{{ shots }}
		 {{ title }}
		{{ /shots }}
	{{ /placid }}

### Example code block with handle
	{{ placid handle="dribbble" }}
		{{ shots }}
		 {{ title }}
		{{ /shots }}
	{{ /placid }}

*If you are unsure as to what tags to use within the placid variable pair, just pop the api url into your browser and work it out from there*

### Queries
You can add queries to the request from the template using a `key:value` pattern separated by commas (`,`),  something like this:

	{{ placid handle="feed" query="posts:5,limit:4" }}
	{{ /placid }}

which will work out something like: `http://someapi.co.uk/feed?posts=5&limit=4`

### Paths
You can change the request path without having to keep overwritting the url.

	{{ placid handle="stripe" path="/v1/customers/{{ id }}" }}
		{{ email }}
	{{ /placid }}

So if you have set the url to something like `https://api.stripe.com/v1/charges` in the config, it would be replaced as `https://api.stripe.com/v1/customers/123`, for example.

### Tokens
To reuse access tokens that are stored in your config simply add the `access_token` parameter with the name of the token you want from `placid_tokens` in the placid config file

	{{ placid handle="githubRepo" access_token="github" }}
	{{ /placid }}	

### Handling no results
You can catch when there are no results just like you would in an entries loop:

	{{ placid url="http://www.dustysquirrels.com/noapi" }}
		{{ if no_results }}
			No results
		{{ else }}
			Squirrels!
		{{ endif }}
	{{ /placid }}

## API
You can utilize Placid in your own plugins via the Statamic plugin API.

### Request 

	$request = $this->addon->api('placid')->request($url)
	
This will return a `GuzzleHttp\Message\Request Object` which you can interact with, read the [Guzzle docs](http://guzzle.readthedocs.org/en/latest/http-messages.html#requests) for more info. If you need a different method to `GET` then just pass it in the second parameter.

### Send  

	$response = $this->addon->api('placid')->send($request)
	
This will send the request and return a `GuzzleHttp\Message\Response Object`, again you can interact with this but if you just want to get the response content you could just do `$response->json()`, check the [Guzzle docs](http://guzzle.readthedocs.org/en/latest/http-messages.html#responses) for more info.

## Support,issues,feedback
If you want to leave feedback about this project, feel free to get in touch on [twitter](http://www.twitter.com/alecritson) if you experience any issues please just create a new issue here on the Repo
