from typing import List, Any

def create_pagination_response(items: List[Any], total: int, limit: int, offset: int):
    """
    Creates a standardized pagination response dictionary.

    :param items: The list of items for the current page.
    :param total: The total number of items across all pages.
    :param limit: The number of items per page.
    :param offset: The starting offset for the query.
    :return: A dictionary containing paginated data and metadata.
    """
    page = (offset // limit) + 1 if limit > 0 else 1
    total_pages = (total + limit - 1) // limit if limit > 0 else (1 if total > 0 else 0)

    return {
        "items": items,
        "total": total,
        "limit": limit,
        "page": page,
        "totalPages": total_pages,
        "hasNext": (offset + limit) < total,
        "hasPrevious": offset > 0,
    }