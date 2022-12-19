#!/usr/bin/env bash
cd "$(dirname "$0")"

rm -rf google

for p in $(find ../third_party/googleapis/google -type f -name *.proto); do
	protoc \
    --proto_path=../third_party/googleapis \
    --php_out=./ \
    --grpc_out=./ \
    --plugin=protoc-gen-grpc="$(which grpc_php_plugin)" \
    "$p"
done
