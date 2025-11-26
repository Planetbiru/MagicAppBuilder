package util

import (
	"context"
	"encoding/json"
	"fmt"
	"graphqlapplication/constant"
	"log"
	"os"
	"path/filepath"
	"strconv"
	"strings"
)

var translations = make(map[string]map[string]string)
var defaultLang string

// InitI18n loads all translation files from the given directory.
func InitI18n(dir string) {
	log.Println("Loading translations...")
	defaultLang = os.Getenv("DEFAULT_LANGUAGE")
	if defaultLang == "" {
		defaultLang = "en" // Fallback if not set in .env
		log.Println("DEFAULT_LANGUAGE not set in .env, using 'en' as default")
	}

	files, err := os.ReadDir(dir)
	if err != nil {
		log.Fatalf("Failed to read i18n directory: %v", err)
	}

	for _, file := range files {
		if filepath.Ext(file.Name()) == ".json" {
			lang := strings.TrimSuffix(file.Name(), ".json")
			filePath := filepath.Join(dir, file.Name())
			content, err := os.ReadFile(filePath)
			if err != nil {
				log.Printf("Failed to read translation file %s: %v", file.Name(), err)
				continue
			}

			var langMap map[string]string
			if err := json.Unmarshal(content, &langMap); err != nil {
				log.Printf("Failed to parse translation file %s: %v", file.Name(), err)
				continue
			}
			translations[lang] = langMap
			log.Printf("Loaded translation for language: %s", lang)
		}
	}

	if _, ok := translations[defaultLang]; !ok {
		log.Fatalf("Default language '%s' translation file not found.", defaultLang)
	}
}

// T is a function to get a translated string.
// It will try the given language, then fallback to the default language if not found.
func T(ctx context.Context, key string, args ...interface{}) string {
	lang, ok := ctx.Value(constant.LanguageKey).(string)
	if !ok || lang == "" {
		lang = defaultLang
	}
	return Translate(lang, key, args...)
}

func Translate(lang string, key string, args ...interface{}) string {
	var message string
	var found bool

	// Try to get the translation from the selected language
	if langMap, ok := translations[lang]; ok {
		if msg, ok := langMap[key]; ok {
			message = msg
			found = true
		}
	}

	// Fallback to the default language if the key is not found in the selected language
	if !found && lang != defaultLang {
		if langMap, ok := translations[defaultLang]; ok {
			if msg, ok := langMap[key]; ok {
				message = msg
				found = true
			}
		}
	}

	if !found {
		// If not found, convert snake_case to Title Case as a fallback.
		words := strings.Split(key, "_")
		for i, word := range words {
			if len(word) > 0 {
				words[i] = strings.ToUpper(string(word[0])) + word[1:]
			}
		}
		return strings.Join(words, " ")
	}

	// Replace placeholders {0}, {1}, etc.
	for i, arg := range args {
		placeholder := "{" + strconv.Itoa(i) + "}"
		message = strings.ReplaceAll(message, placeholder, fmt.Sprint(arg))
	}
	return message
}
