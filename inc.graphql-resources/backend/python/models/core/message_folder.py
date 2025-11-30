from sqlalchemy import Column, String, Integer, Boolean
from database import Base

class MessageFolder(Base):
    __tablename__ = 'message_folder'

    message_folder_id = Column(String(40), primary_key=True)
    name = Column(String(100), nullable=False)
    sort_order = Column(Integer, default=0)
    active = Column(Boolean, default=True)