from sqlalchemy import Column, Integer, String, Text, Boolean, DateTime, ForeignKey
from database import Base
from datetime import datetime

class Notification(Base):
    __tablename__ = 'notification'

    notification_id = Column(String(40), primary_key=True)
    admin_id = Column(Integer, ForeignKey('admin.admin_id'), nullable=True)
    admin_group = Column(String(40), ForeignKey('admin_level.admin_level_id'), nullable=True)
    subject = Column(String(255), nullable=False)
    content = Column(Text)
    link = Column(String(255))
    time_create = Column(DateTime, default=datetime.utcnow)
    is_read = Column(Boolean, default=False)
    time_read = Column(DateTime, nullable=True)
    ip_read = Column(String(50), nullable=True)