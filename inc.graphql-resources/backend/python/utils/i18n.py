import json
from pathlib import Path
from typing import Callable, Dict, Any

from fastapi import Request

# Cache to store loaded translation data
_translations: Dict[str, Dict[str, str]] = {}
_base_dir = Path(__file__).resolve().parent.parent / "static" / "langs" / "i18n"


def _load_translation(lang: str) -> Dict[str, str]:
    """Loads the translation JSON file for a specific language."""
    if lang in _translations:
        return _translations[lang]

    lang_file = _base_dir / f"{lang}.json"
    if not lang_file.exists():
        # Fallback to English if the file is not found
        lang = "en"
        lang_file = _base_dir / "en.json"

    try:
        with open(lang_file, "r", encoding="utf-8") as f:
            translation_data = json.load(f)
            _translations[lang] = translation_data
            return translation_data
    except (IOError, json.JSONDecodeError):
        # If an error occurs, return an empty dictionary
        return {}


async def get_translator(request: Request) -> Callable[..., str]:
    """FastAPI dependency to get the translator function based on the request header."""
    lang = request.headers.get("X-Language-Id", "en")
    translations = _load_translation(lang)

    def translator(key: str, *args: Any) -> str:
        """Translates a key and formats the string if arguments are provided."""
        return translations.get(key, key).format(*args)

    return translator
