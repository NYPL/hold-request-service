# NYPL Hold Request Service

[![Build Status](https://travis-ci.org/NYPL/hold-request-service.svg?branch=master)](https://travis-ci.org/NYPL/hold-request-service)
[![Coverage Status](https://coveralls.io/repos/github/NYPL/hold-request-service/badge.svg?branch=master)](https://coveralls.io/github/NYPL/hold-request-service?branch=master)

This package is intended to be used as a Lambda-based Hold Request Service using the [NYPL PHP Microservice Starter](https://github.com/NYPL/php-microservice-starter).

This package adheres to [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), and [PSR-4](http://www.php-fig.org/psr/psr-4/) (using the [Composer](https://getcomposer.org/) autoloader).

## Service Responsibilities

The Hold Request Service receives a request from the Discovery interface
and processes the intended action. A request can be for a hold in Sierra
or ReCAP.

Once the service validates the request and saves it to its database instance,
it sends its Avro encoded data to a Kinesis stream and returns a successful
response or returns an error response.

After these responsibilities are met, the contract ends and another
service takes over or the request terminates in a response from this
service.

The Kinesis stream can be consumed in order to further process a hold
request via a Sierra API service or ReCAP API service. These services
will further process the hold request and make modifications, if necessary.
These downstream services can update with information garnered from the
APIs mentioned. These APIs can modify the Hold Request by changing its
status.

A Hold Request is governed by the following Avro 1.8.1 schema:

## Schema
~~~
{
  "patron": "string",
  "requestType": "string",
  "nyplSource": "string",
  "recordType": "string",
  "record": "string",
  "pickupLocation": "string",
  "neededBy": "string",
  "numberOfCopies": "int",
  "docDeliveryData": {
    "chapterTitle": "string",
    "emailAddress": "string",
    "startPage": "string",
    "endPage": "string",
    "issue": "string",
    "volume": "string"
  }
}
~~~

## Hold Request Data

* recordType - (b, i, j)
* record - identifier [bibId (b), itemId (i), volumeId (j)]
* pickupLocation - NYPL location identifier
* neededBy - date when hold is terminated (business rules?)
* numberOfCopies - should always be 1 for ReCAP

## ReCAP API RequestItem

(https://uat-recap.htcinc.com:9093/swagger-ui.html#/)

See also:
 * [Diagram of NYPL Request architecture](https://docs.google.com/presentation/d/1Tmb53yOUett1TLclwkUWa-14EOG9dujAyMdLzXOdOVc/edit#slide=id.g330b256cdf_0_0)
 * [Detailed description of hold request scenarios with reference to above diagram](https://docs.google.com/document/d/1AMqdUlKn5gV6o98JXfD2SjbIUZm04aGKXtupnmvJUN8/edit#heading=h.br4pvk4ymn9s)
 * [Flow diagram documenting how item & EDD manifest across NYPL & HTC systems](https://docs.google.com/presentation/d/1G9wCyRswefgu4IvN6pn8ntuSVxJ6eEwYDzsdexTfHS8/edit#slide=id.g2a59ba2c93_0_439)
 * [HTC API wiki](https://htcrecap.atlassian.net/wiki/spaces/RTG/pages/25438542/Request+Item)

## ReCAP Request Data

* All require requestType, itemBarcodes, patronBarcode, trackingId, bibId,
itemOwningInstitution, requestingInstitution
* Retrieval/Recall related: author, callNumber, deliveryLocation
* Temp record related: titleIdentifier
* EDD requires email
* EDD related: startPage, endPage, volume, issue, chapterTitle

## Requirements

* Node.js >=6.0
* PHP >=7.0
  * [pdo_pdgsql](http://php.net/manual/en/ref.pdo-pgsql.php)

Homebrew is highly recommended for PHP:
  * `brew install php71`
  * `brew install php71-pdo-pgsql`

## Installation

1. Clone the repo.
2. Install required dependencies.
   * Run `npm install` to install Node.js packages.
   * Run `composer install` to install PHP packages.

## Security

Authorization provided via OAuth2 authorization_code. Set scopes in the format of access_type:service.
For example, read:holds to access the GET request method endpoints.

## Configuration

Various files are used to configure and deploy the Lambda.

### .env

`.env` is used *locally* for by `node-lambda` for deploying to and configuring Lambda in *all* environments. You should use this file to configure the common settings for the Lambda (e.g. timeout, role, etc.)

### package.json

Configures `npm run` deployment commands for each environment and sets the proper AWS Lambda VPC and
security group.

~~~~
"scripts": {
  "deploy-development": "./node_modules/.bin/node-lambda deploy -e development -f config/var_development.env -S config/event_sources_development.json --role arn:aws:iam::224280085904:role/lambda_basic_execution --profile nypl-sandbox -b subnet-f4fe56af -g sg-1d544067",
  "deploy-qa": "./node_modules/.bin/node-lambda deploy -e qa -f config/var_qa.env -S config/event_sources_qa.json --role arn:aws:iam::946183545209:role/lambda-full-access --profile nypl-digital-dev -b subnet-f35de0a9,subnet-21a3b244 -g sg-aa74f1db",
  "deploy-production": "./node_modules/.bin/node-lambda deploy -e production -f config/var_production.env -S config/event_sources_production.json --role arn:aws:iam::946183545209:role/lambda-full-access --profile  nypl-digital-dev -b subnet-59bcdd03,subnet-5deecd15 -g sg-116eeb60"
},
~~~~

### var_app

Configures environment variables common to *all* environments.

### var_*environment*.env

Configures environment variables specific to each environment.

### event_sources_*environment*.json

Configures Lambda event sources (triggers) specific to each environment.

## Usage

### Sample Events

This lambda has three events:
- DiscoveryEvent.json, simulating a hold request from Discovery UI
- RecapEvent.json, simulating a hold request from Recap UI
- EddEvent.json, simulating an edd request from Discovery UI.

### DiscoveryEvent: simulating a hold request from Discovery UI

1. Because the event lacks a deliveryLocation, HoldRequestConsumer interprets the event as originating from the Discovery UI
2. HoldRequestConsumer calls the Recap API
3. If the Recap API returns a success response, HoldRequestConsumer does nothing.
4. If the Recap API returns a failure response, HoldRequestConsumer records the error in HoldRequestResult stream. In this case the HoldRequestResultConsumer notifies the HoldRequestService that the hold has not been processed

The existing DiscoveryEvent.json should return a success response (i.e. HoldRequestConsumer should do nothing)

### RecapEvent: simulating a hold request from Recap UI

1. Because the event contains a deliveryLocation, HoldRequestConsumer interprets the event as a Recap UI originating event and filters it out (i.e. does nothing with it).   (Presumably ReCAP already knows about the hold request represented.)

### EddEvent: simulating an edd request from Discovery UI

1. An EDD event causes the Consumer to hit the ReCAP API and post a version of the result to the HoldRequestResult stream
2. The HoldRequestResultConsumer reads the HoldRequestResult stream looking specifically for EDD requests in-process, and sends an email via SES.
3. If SES returns success, the HoldRequest object has `.processed` set to TRUE and a PATCH is sent to HoldRequestService. (HoldRequestService updates the record and broadcasts the change via the HoldRequest stream - which is ignored by HoldRequestConsumer because processed === TRUE.)

Because the edd request in the `sampleEvents` file has already been processed, you will need to substitute an available item id in order to get a completely successful run for all the downstream components (i.e. to the point where HoldRequestResultConsumer sends an email)

### Process a Lambda Event

To use `node-lambda` to process the sample API Gateway event in `event.json`, run:

~~~~
node-lambda run -j [eventfile] -f [configfile]
~~~~

### Run as a Web Server

To use the PHP internal web server, run:

~~~~
php -S localhost:8888 -t . index.php
~~~~

Note you'll need to:
 * Copy your desired `var_{environment}.env` file to `var_app` (e.g. `cp config/var_qa.env config/var_app`) (**Note** `var_app` must not end in `.env`)
 * Add your AWS creds (`AWS_ACCESS_KEY_ID=`, `AWS_SECRET_ACCESS_KEY=`) to `config/var_app`
 * Make sure all variables in `var_app` are decrypted

You can then make a request to the Lambda: `http://localhost:8888/api/v0.1/hold-requests`.

### Swagger Documentation Generator

Create a Swagger route to generate Swagger specification documentation:

~~~~
$service->get("/docs", SwaggerGenerator::class);
~~~~

## Contributing

This app follows a [Development-QA-Master](https://github.com/NYPL/engineering-general/blob/git-workflows/standards/git-workflow.md#development-qa-master) Git Workflow; Cut feature branches from `development`, promote to `qa` followed by `master`.
