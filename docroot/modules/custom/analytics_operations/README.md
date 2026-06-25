# Analytics Operations

Module to house code for various Analytics admin operations.

## How to create a new operation

1. Add the route to `analytics_operations.routing.yml`. If possible, simpify your actions to basic CRUD operations.
2. Add the form class in the `src/Form` directory. This form will be used to trigger the operation.
3. Add the operation class to the `src` directory. This is the model for the operation, all business logic should be here.

## How to run an operation

### TODO

- Create a sample operation that can be used to scaffold new operations from.
