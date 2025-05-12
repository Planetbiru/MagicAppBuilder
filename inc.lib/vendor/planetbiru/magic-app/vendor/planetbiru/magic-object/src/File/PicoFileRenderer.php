<?php

namespace MagicObject\File;

/**
 * Class PicoFileRenderer
 *
 * Utility class to render various file types (images, audio, video, files, links, text)
 * into corresponding HTML elements from plain strings or JSON-encoded arrays.
 */
class PicoFileRenderer
{
    /**
     * Decodes a JSON string into an array or returns the original string if decoding fails.
     *
     * @param string $input The JSON string or plain string.
     * @return array|string|null Returns an array if valid JSON array, original string if not,
     *                           or null if input is empty.
     */
    public static function decodeJson($input)
    {
        if (!isset($input) || empty($input)) {
            return null;
        }

        $decoded = json_decode($input, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return $input;
    }

    /**
     * Renders HTML <img> tags from image path(s).
     *
     * @param string|array $imagePath A string or JSON-encoded array of image paths.
     * @return string HTML <img> elements with the "pico-image" class.
     */
    public static function renderImage($imagePath)
    {
        $decoded = self::decodeJson($imagePath);
        $html = '';

        if (isset($decoded)) {
            if (is_array($decoded)) {
                foreach ($decoded as $value) {
                    $value = self::decodeJson($value);
                    if (is_string($value)) {
                        $html .= '<span class="pico-image-container"><img src="' . htmlspecialchars($value) . '" alt="Image" class="pico-image" /></span>' . "\n";
                    }
                }
            } else {
                $html .= '<span class="pico-image-container"><img src="' . htmlspecialchars($decoded) . '" alt="Image" class="pico-image" /></span>' . "\n";
            }
        }

        return $html;
    }

    /**
     * Renders HTML <audio> tags from audio path(s).
     *
     * @param string|array $audioPath A string or JSON-encoded array of audio paths.
     * @return string HTML <audio> elements with the "pico-audio" class.
     */
    public static function renderAudio($audioPath)
    {
        $decoded = self::decodeJson($audioPath);
        $html = '';

        if (isset($decoded)) {
            if (is_array($decoded)) {
                foreach ($decoded as $value) {
                    $value = self::decodeJson($value);
                    if (is_string($value)) {
                        $html .= '<span class="pico-audio-container"><audio controls class="pico-audio"><source src="' . htmlspecialchars($value) . '" type="audio/mpeg">Your browser does not support the audio tag.</audio></span>' . "\n";
                    }
                }
            } else {
                $html .= '<span class="pico-audio-container"><audio controls class="pico-audio"><source src="' . htmlspecialchars($decoded) . '" type="audio/mpeg">Your browser does not support the audio tag.</audio></span>' . "\n";
            }
        }

        return $html;
    }

    /**
     * Renders HTML <video> tags from video path(s).
     *
     * @param string|array $videoPath A string or JSON-encoded array of video paths.
     * @return string HTML <video> elements with the "pico-video" class.
     */
    public static function renderVideo($videoPath)
    {
        $decoded = self::decodeJson($videoPath);
        $html = '';

        if (isset($decoded)) {
            if (is_array($decoded)) {
                foreach ($decoded as $value) {
                    $value = self::decodeJson($value);
                    if (is_string($value)) {
                        $html .= '<span class="pico-video-container"><video controls class="pico-video"><source src="' . htmlspecialchars($value) . '" type="video/mp4">Your browser does not support the video tag.</video></span>' . "\n";
                    }
                }
            } else {
                $html .= '<span class="pico-video-container"><video controls class="pico-video"><source src="' . htmlspecialchars($decoded) . '" type="video/mp4">Your browser does not support the video tag.</video></span>' . "\n";
            }
        }

        return $html;
    }

    /**
     * Renders downloadable file link(s) as HTML <a> tags.
     *
     * @param string|array $filePath A string or JSON-encoded array of file paths.
     * @return string HTML <a> elements with the "pico-file" class and download attribute.
     */
    public static function renderFile($filePath)
    {
        $decoded = self::decodeJson($filePath);
        $html = '';

        if (isset($decoded)) {
            if (is_array($decoded)) {
                foreach ($decoded as $value) {
                    $value = self::decodeJson($value);
                    if (is_string($value)) {
                        $html .= '<a href="' . htmlspecialchars($value) . '" download class="pico-file">' . basename($value) . '</a>' . "\n";
                    }
                }
            } else {
                $html .= '<a href="' . htmlspecialchars($decoded) . '" download class="pico-file">' . basename($decoded) . '</a>' . "\n";
            }
        }

        return $html;
    }

    /**
     * Renders URL link(s) as HTML <a> tags.
     *
     * @param string|array $linkPath A string or JSON-encoded array of URLs.
     * @return string HTML <a> elements with the "pico-link" class.
     */
    public static function renderLink($linkPath)
    {
        $decoded = self::decodeJson($linkPath);
        $html = '';

        if (isset($decoded)) {
            if (is_array($decoded)) {
                foreach ($decoded as $value) {
                    $value = self::decodeJson($value);
                    if (is_string($value)) {
                        $html .= '<a href="' . htmlspecialchars($value) . '" class="pico-link">' . basename($value) . '</a>' . "\n";
                    }
                }
            } else {
                $html .= '<a href="' . htmlspecialchars($decoded) . '" class="pico-link">' . basename($decoded) . '</a>' . "\n";
            }
        }

        return $html;
    }

    /**
     * Renders text content as HTML <p> tags.
     *
     * @param string|array $text A string or JSON-encoded array of text content.
     * @return string HTML <p> elements with the "pico-text" class.
     */
    public static function renderText($text)
    {
        $decoded = self::decodeJson($text);
        $html = '';

        if (isset($decoded)) {
            if (is_array($decoded)) {
                foreach ($decoded as $value) {
                    $value = self::decodeJson($value);
                    if (is_string($value)) {
                        $html .= '<p class="pico-text">' . htmlspecialchars($value) . '</p>' . "\n";
                    }
                }
            } else {
                $html .= '<p class="pico-text">' . htmlspecialchars($decoded) . '</p>' . "\n";
            }
        }

        return $html;
    }
}
