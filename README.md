# Survey links block

A block to fetch links to a survey from an external api.

## Versions and branches

| Moodle Version   | Branch            | 
|------------------|-------------------|
| Moodle 3.9 - 4.1 | main              | 
| Moodle 4.4+      | MOODLE_404_STABLE | 

## Set up

Go to `site administration` -> `plugins` -> `blocks` -> `Survey links` and provide a base uri and api secret to fetch the survey links from.

The api key is currently set to use a subscription key: `Ocp-Apim-Subscription-Key`.

## Changing the HTTP client

If you need to change the http client from guzzle, add a new class named `classes/newname_client` and implement the `\block_surveylinks\http_client_interface`. You will need to update which client the explorance_api uses as well. 

## Capabilities

One capability is provided in addition to the standard block capabilities. - `block/surveylinks:viewmysurveylinks`

Only users with this capability will be able to fetch survey links from the external api. By default, only students are given this capability. 


