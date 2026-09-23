package main

import (
	"log"
	"os"

	socks5 "github.com/armon/go-socks5"
)

func main() {
	config := socks5.Config{
		AuthMethods: []socks5.Authenticator{socks5.UserPassAuthenticator{Credentials: socks5.StaticCredentials{"user": "pass"}}},
		Logger:      log.New(os.Stderr, "", log.LstdFlags|log.LUTC|log.Lshortfile),
	}

	server, err := socks5.New(&config)
	if err != nil {
		panic(err)
	}

	if err = server.ListenAndServe("tcp", "127.0.0.1:8080"); err != nil {
		panic(err)
	}
}
