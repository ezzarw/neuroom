package main

import (
	"database/sql"
	"fmt"
	"log"
	"math/rand/v2"
	"os"
	"strconv"
	"strings"

	_ "github.com/go-sql-driver/mysql"
)

func getEnvOrDefault(key string, fallback string) string {
	value := strings.TrimSpace(os.Getenv(key))
	if value == "" {
		return fallback
	}
	return value
}

func buildDSN() string {
	host := getEnvOrDefault("DB_HOST", "127.0.0.1")
	port := getEnvOrDefault("DB_PORT", "3306")
	database := getEnvOrDefault("DB_DATABASE", "neuroom_db")
	username := getEnvOrDefault("DB_USERNAME", "root")
	password := os.Getenv("DB_PASSWORD")

	return fmt.Sprintf("%s:%s@tcp(%s:%s)/%s", username, password, host, port, database)
}

func main() {
	if len(os.Args) < 2 {
		log.Fatal("usage: suffix_username <username>")
	}

	baseUsername := strings.ToLower(strings.ReplaceAll(strings.Join(os.Args[1:], ""), " ", ""))
	if baseUsername == "" {
		log.Fatal("username must not be empty")
	}

	db, err := sql.Open("mysql", buildDSN())
	if err != nil {
		log.Fatal(err)
	}
	defer db.Close()

	if err := db.Ping(); err != nil {
		log.Fatal(err)
	}

	results, err := db.Query("SELECT username FROM authentications")
	if err != nil {
		log.Fatal(err)
	}
	defer results.Close()

	usernames := map[string]struct{}{}

	for results.Next() {
		var username string
		if err := results.Scan(&username); err != nil {
			log.Fatal(err)
		}
		usernames[username] = struct{}{}
	}

	if err := results.Err(); err != nil {
		log.Fatal(err)
	}

	if _, exists := usernames[baseUsername]; !exists {
		fmt.Println(baseUsername)
		return
	}

	for {
		candidate := baseUsername + strconv.Itoa(rand.IntN(10000))
		if _, exists := usernames[candidate]; !exists {
			fmt.Println(candidate)
			return
		}
	}
}
