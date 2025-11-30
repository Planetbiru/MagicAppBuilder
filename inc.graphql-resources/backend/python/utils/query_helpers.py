def apply_filters(stmt, model, filter_list):
    """Applies a list of filters to a SQLAlchemy query statement."""
    if not filter_list:
        return stmt

    where_clauses = []
    for f in filter_list:
        field_name = f.get('field')
        value = f.get('value')
        operator = f.get('operator', 'EQUALS').upper()
        
        column = getattr(model, field_name, None)
        if column is None:
            continue

        if operator == 'EQUALS':
            where_clauses.append(column == value)
        elif operator == 'NOT_EQUALS':
            where_clauses.append(column != value)
        elif operator == 'CONTAINS':
            where_clauses.append(column.ilike(f"%{value}%"))
        elif operator == 'GREATER_THAN':
            where_clauses.append(column > value)
        elif operator == 'GREATER_THAN_OR_EQUALS':
            where_clauses.append(column >= value)
        elif operator == 'LESS_THAN':
            where_clauses.append(column < value)
        elif operator == 'LESS_THAN_OR_EQUALS':
            where_clauses.append(column <= value)

    if where_clauses:
        stmt = stmt.where(*where_clauses)
    return stmt

def apply_ordering(stmt, model, order_by_list):
    """Applies a list of ordering clauses to a SQLAlchemy query statement."""
    if order_by_list:
        order_clauses = [getattr(model, o['field']).asc() if o.get('direction', 'ASC').upper() == 'ASC' else getattr(model, o['field']).desc() for o in order_by_list]
        stmt = stmt.order_by(*order_clauses)
    return stmt