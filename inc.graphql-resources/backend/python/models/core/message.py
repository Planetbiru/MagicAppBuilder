from sqlalchemy import Column, Integer, String, Text, Boolean, DateTime, ForeignKey
from sqlalchemy.orm import relationship
from database import Base
from datetime import datetime

class Message(Base):
    __tablename__ = 'message'

    message_id = Column(String(40), primary_key=True)
    message_folder_id = Column(String(40), ForeignKey('message_folder.message_folder_id'))
    sender_id = Column(String(40), ForeignKey('admin.admin_id'))
    receiver_id = Column(String(40), ForeignKey('admin.admin_id'))
    subject = Column(String(255), nullable=False)
    content = Column(Text)
    time_create = Column(DateTime, default=datetime.utcnow)
    is_read = Column(Boolean, default=False)
    time_read = Column(DateTime)

    sender = relationship("Admin", foreign_keys=[sender_id])
    receiver = relationship("Admin", foreign_keys=[receiver_id])