# Migrating to Swagger-PHP v3.x

Swagger-PHP 3.x generates a openapi.json file that follows the [OpenAPI Version 3.0.x Specification](https://github.com/OAI/OpenAPI-Specification).

If you need to output the older 2.x specification use OpenApi-php 2.x

## The default output changed from json to yaml

This aligns better with the direction of the swagger documentation and examples.
Annotations can't be used as string anymore, you'll need to call `toYaml()` or `toJson()` if you prefer the json format.

## Updated CLI

- Added colors
- No output for succesful execution (Removed summary)
- non-zero exit when an error occured.
- Defaults to yaml
- Defaults to stdout. To save to openapi.yaml use `-o` or `>`

## Changed annotations

### SWG is renamed to OA

The namespace is renamed from SWG (Swagger) to OA (OpenApi)

### @SWG\Swagger() is renamed to @OA\OpenApi()

### @SWG\Path() is renamed to @OA\PathItem()

The specification uses the term "Path Item Object", updated the annotation to reflect that.

### @SWG\Definition() is removed

Use @OA\Schema() instead of @OA\Definition() and update the references from "#/definitions/something" to "#/components/schemas/something".

### @SWG\Path is removed

Use @OA\PathItem instead of @SWG\Path and update references.

### Consumes, produces field is removed from OpenAPI specification

Use @OA\MediaType to set data format.

### Rename parameter references

Rename `#/parameters/{parameter_name}` to `#/components/parameters/{parameter_name}`

### Rename response references

Rename `#/responses/{response}` to `#/components/responses/{response}`

### Renamed cli

Renamed swagger to openapi

### More details about differences:

[A Visual Guide to What's New in Swagger 3.0](https://blog.readme.io/an-example-filled-guide-to-swagger-3-2/)
