package util

import (
	"crypto/sha1"
	"encoding/hex"
	"net"
	"net/http"
	"strings"
)

// DoubleSha1 calculates sha1(sha1(password)).
func DoubleSha1(input string) string {
	h1 := sha1.New()
	h1.Write([]byte(input))
	step1 := hex.EncodeToString(h1.Sum(nil))

	h2 := sha1.New()
	h2.Write([]byte(step1))
	return hex.EncodeToString(h2.Sum(nil))
}

// GetClientIP extracts the client's real IP address from the request.
func GetClientIP(r *http.Request) string {
	// Check for X-Forwarded-For header, which can be a comma-separated list.
	// The client's IP is typically the first one.
	if forwardedFor := r.Header.Get("X-Forwarded-For"); forwardedFor != "" {
		// Split the list and return the first IP
		ips := strings.Split(forwardedFor, ",")
		if len(ips) > 0 {
			return strings.TrimSpace(ips[0])
		}
	}

	// Check for X-Real-IP header.
	if realIP := r.Header.Get("X-Real-IP"); realIP != "" {
		return realIP
	}

	// Fallback to the remote address.
	ip, _, _ := net.SplitHostPort(r.RemoteAddr)
	return ip
}
