package main

import (
	"database/sql"
	"fmt"
	"log"
	"math/rand/v2"
	"os"
	"slices"
	"strconv"
	"strings"

	_ "github.com/go-sql-driver/mysql"
)

func main() {
	db, err := sql.Open("mysql", "root:@tcp(127.0.0.1:3306)/neuroom_db")
	if err != nil {
		log.Fatal(err)
	}
	defer db.Close()

	if err := db.Ping(); err != nil {
		log.Fatal(err)
	}

	// 	users
	results, err := db.Query("SELECT username FROM users")
	if err != nil {
		log.Fatal(err)
	}
	defer results.Close()

	usernames := []string{}

	for results.Next() {
		var username string
		if err := results.Scan(&username); err != nil {
			log.Fatal(err)
		}
		usernames = append(usernames, username)
	}

	if err := results.Err(); err != nil {
		log.Fatal(err)
	}

	// fmt.Println(usernames)
	username_input := strings.Join(os.Args[1:], "")
	iteration := 0

	for !slices.Contains(usernames, username_input) && iteration == 0 {
		username_suffixed := strings.ToLower(strings.ReplaceAll(username_input, " ", "") + strconv.Itoa(rand.IntN(9999)))
		fmt.Println(username_suffixed)
		iteration = iteration + 1
	}
}
