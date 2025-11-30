from sqlalchemy import Column, String, Integer, Boolean
from database import Base

class AdminLevel(Base):
    __tablename__ = 'admin_level'

    admin_level_id = Column(String(40), primary_key=True)
    name = Column(String(100), nullable=False)
    sort_order = Column(Integer, default=0)
    active = Column(Boolean, default=True)