import os
import string
import random

SQLALCHEMY_DATABASE_URI = "sqlite:///{}".format(os.path.join(os.path.dirname(os.path.abspath(__file__)), "timesheets.db"))
SQLALCHEMY_TRACK_MODIFICATIONS = True
SQLALCHEMY_ECHO = True
SECRET_KEY = ''.join(random.choices(string.ascii_letters + string.digits + string.punctuation, k=16))
