# pipelines-microservice-CLI

With this tool you can interact with the pipelines-microservice using the command line.

## Using the tool

1. Create a config file named `PipelineManagerAPICommand.local.yml` in the folder `src/resources`. The only property this file contains is `base_uri: http://location.of.my.pipelines.microservice`.
2. On the command line do from the root folder of the project: `./src/Application.php` . This will show you the list of commands available to interact with the pipelines-microservice.

## Running the tests

### Running the functional tests

From the root of the project just type `phpunit` and the tests will run.


### Running the integration tests

1. Make sure you have a running pipelines-microservice, to have one see [pipelines-microservice](https://github.com/InSilicoDB/pipelines-microservice)
2. Change the `base_uri` property in the file `src/resources/PipelineManagerAPICommand.integration-test.yml` to the uri of the pipelines-microservice you setup in step one.
3. Execute from the root folder of the project `phpunit -c phpunit.integration.xml`
