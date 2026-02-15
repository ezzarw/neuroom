package main

import (
	"fmt"
	"os"
	"strings"

	"golang.org/x/crypto/bcrypt"
)

const defaultCost = 12

func isLikelyBcryptHash(hash string) bool {
	if len(hash) != 60 {
		return false
	}

	return strings.HasPrefix(hash, "$2a$") ||
		strings.HasPrefix(hash, "$2b$") ||
		strings.HasPrefix(hash, "$2y$")
}

func usage() {
	fmt.Println("Usage: hashingbcry [OPTIONS] [VALUES]")
	fmt.Println("Options:")
	fmt.Println("-e\t\tEncrypting/Hashing from the password")
	fmt.Println("-v\t\tValidate hash from the password")
	fmt.Println()
	fmt.Println("Values:")
	fmt.Println("-e\t\t[YOUR_PASSWORD]")
	fmt.Println("-v\t\t[YOUR_PASSWORD] [HASHED_PASSWORD]")
	fmt.Println()
	fmt.Println("Usage example:")
	fmt.Println("hashingbcry -e mypassword123")
	fmt.Println("hashingbcry -v mypassword123 '$2a$12$m00W05Zhaf5b/6.9NNtO2.QYxguYWcH8xcN.9OKGU6j/G09Jk3GA6'")
}

func main() {
	if len(os.Args) < 2 {
		usage()
		os.Exit(1)
	}

	first_argument := os.Args[1]

	switch first_argument {
	case "-e":
		if len(os.Args) != 3 {
			usage()
			os.Exit(1)
		}

		password := os.Args[2]
		hashedPassword, err := bcrypt.GenerateFromPassword([]byte(password), defaultCost)
		if err != nil {
			fmt.Println("error:", err)
			os.Exit(1)
		}
		fmt.Println(string(hashedPassword))

	case "-v":
		if len(os.Args) != 4 {
			usage()
			os.Exit(1)
		}

		password := os.Args[2]
		hashedPasswordInput := os.Args[3]

		if !isLikelyBcryptHash(hashedPasswordInput) {
			fmt.Println("format invalid.")
			fmt.Println("Hint: bungkus hash dengan single quote")
			fmt.Println("Contoh: hashingbcry -v mypassword '$2a$12$.................................................'")
			os.Exit(1)
		}

		if err := bcrypt.CompareHashAndPassword([]byte(hashedPasswordInput), []byte(password)); err != nil {
			fmt.Println("Bcrypt not matched")
			os.Exit(1)
		}

		fmt.Println("Bcrypt matched")

	default:
		usage()
		os.Exit(1)
	}
}
