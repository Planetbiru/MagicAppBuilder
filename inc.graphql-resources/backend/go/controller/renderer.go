package controller

import (
	"context"
	"graphqlapplication/constant"
	"graphqlapplication/util"
	"html/template"
	"net/http"
	"path/filepath"
)

// renderTemplate is a helper function that parses and executes an HTML template with the given data.
// It injects several utility functions into the template, including internationalization (i18n) and pagination helpers.
func renderTemplate(w http.ResponseWriter, r *http.Request, tmplName string, data interface{}) {
	ctx := r.Context()
	// Determine the language from the request header, defaulting to 'en'.
	lang := r.Header.Get("X-Language-Id")
	if lang == "" {
		lang = "en"
	}
	// Add the language to the context for the translation utility.
	ctx = context.WithValue(ctx, constant.LanguageKey, lang)

	// i18nFunc is a closure that provides translation capabilities to the template.
	i18nFunc := func(key string, args ...interface{}) string {
		return util.T(ctx, key, args...)
	}

	// truncateFunc shortens a string to a specified length and appends '...'.
	truncateFunc := func(s string, length int) string {
		if len(s) > length {
			return s[:length] + "..."
		}
		return s
	}

	// Basic arithmetic functions for pagination logic within the template.
	add := func(a, b int) int { return a + b }
	sub := func(a, b int) int { return a - b }

	// seqFunc creates a sequence of integers, useful for generating pagination links.
	seqFunc := func(start, end int) []int {
		var s []int
		for i := start; i <= end; i++ {
			s = append(s, i)
		}
		return s
	}

	tmplPath := filepath.Join("template", tmplName)
	// Create a new template and register all the helper functions.
	tmpl, err := template.New(filepath.Base(tmplPath)).Funcs(template.FuncMap{
		"T":        i18nFunc,
		"truncate": truncateFunc,
		"add":      add,
		"sub":      sub,
		"seq":      seqFunc,
	}).ParseFiles(tmplPath)

	if err != nil {
		// If parsing fails, send an internal server error.
		http.Error(w, util.T(ctx, "template_error", err.Error()), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	// Execute the template, writing the output to the http.ResponseWriter.
	err = tmpl.Execute(w, data)
	if err != nil {
		// If execution fails, send an internal server error.
		http.Error(w, util.T(ctx, "template_execution_error", err.Error()), http.StatusInternalServerError)
	}
}
