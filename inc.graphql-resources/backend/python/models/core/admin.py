from sqlalchemy import Column, String, Boolean, Date, Text, DateTime
from database import Base

class Admin(Base):
    __tablename__ = 'admin'

    admin_id = Column(String(40), primary_key=True)
    name = Column(String(100))
    username = Column(String(100), unique=True)
    password = Column(String(512))
    password_version = Column(String(512))
    admin_level_id = Column(String(40))
    gender = Column(String(2))
    birth_day = Column(Date)
    email = Column(String(100))
    phone = Column(String(100))
    language_id = Column(String(40))
    validation_code = Column(Text)
    last_reset_password = Column(DateTime)
    blocked = Column(Boolean, default=False)
    time_create = Column(DateTime)
    time_edit = Column(DateTime)
    admin_create = Column(String(40))
    admin_edit = Column(String(40))
    ip_create = Column(String(50))
    ip_edit = Column(String(50))
    active = Column(Boolean, default=True)