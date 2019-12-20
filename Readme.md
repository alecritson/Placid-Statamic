# Placid Statamic

[![StyleCI](https://styleci.io/repos/25640354/shield?branch=v2)](https://styleci.io/repos/25640354)

---

Placid allows you to consume RESTful APIs in your Statamic templates, using Guzzle to handle the requests. 

**Placid supports**:
- Caching/Refreshing
- Template variable pairs
- Predefined requests
- Headers
- Access tokens

### Installation

Require the addon via composer

```
composer require ritson/placid-statamic
```

### Parameters
- **host**: The API host
- **cache** (number): The time in seconds until the cache refreshes (default is 7200 / 2 hours)
- **handle** (string) : The handle of the resource to use
- **method** (string) : You can set which method to use on the request, default is 'GET' 
- **query** (string)  : Add your queries here, see [queries](#queries) for more info
- **path** (string) : Add your own custom path, see [paths](#paths) for details
- **auth** (string) : Handle for the auth scheme to use

### Saved requests
You can set up requests for placid in **resources/placid/requests** like so:

``` yaml
// resources/placid/requests/placeholder.yaml
host: https://jsonplaceholder.typicode.com
method: GET
path: posts/:id
auth: placeholder // See Authentication section
segments:
  id: 1
headers:
  accept: application/json
query:
  foo: bar
formParams:
  foo: bar
```
		
#### Authentication
You can define authorisation schemes to use and reuse on your requests. You define them in **resources/placid/auth** like so:

``` yaml
// resources/placid/auth/placeholder.yaml
headers:
  Authorization: Bearer :token
token: services.api.token
```

The above will send the token through the headers, you define your tokens in your config and reference as you would any Laravel config item.

If you need to send your access token through the query string, define your auth scheme like so:

``` yaml
// resources/placid/auth/placeholder.yaml
query:
  access_token: :token
token: services.api.token
```

The query string and headers will be merged with any that are already present on the request

## Usage

To use this plugin in your templates, simply use these tags:

### Basic example
 
```
{{ placid handle="placeholder" }}
  {{ response.data }}
    <h1>{{ title }}</h1>
  {{ /response.data }}
{{ /placid }}
```

### Full example

```
{{ placid host="https://jsonplaceholder.typicode.com" path=":part/:id" cache="60" query="foo:bar|bar:baz" segments="part:posts|id:1" headers="foo:bar" }}
  {{ response.data }}
    <h1>{{ title }}</h1>
  {{ /response.data }}
{{ /placid }}
```

## Support,issues,feedback
If you want to leave feedback about this project, feel free to get in touch on [twitter](http://www.twitter.com/alecritson) if you experience any issues please just create a new issue here on the Repo
