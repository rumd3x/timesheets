import random
import string

from flask_login import UserMixin
from werkzeug.security import generate_password_hash, check_password_hash

from app import db, login_manager

@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))

class User(UserMixin, db.Model):
    __tablename__ = 'users'

    id = db.Column(db.Integer, primary_key=True)
    email = db.Column(db.String(60), index=True, unique=True)
    password_hash = db.Column(db.String(128))
    display_name = db.Column(db.String(60), index=True)
    timestamps = db.relationship('Timestamp', backref='user', lazy='dynamic')
    api_key = db.Column(db.String(50), nullable=True, default=''.join(random.choices(string.ascii_letters + string.digits + string.punctuation, k=50)))

    @property
    def password(self):
        raise AttributeError('Password is not a readable attribute.')

    @password.setter
    def password(self, password):
        self.password_hash = generate_password_hash(password)

    def verify_password(self, password):
        return check_password_hash(self.password_hash, password)

    def __repr__(self):
        return '<User: {}>'.format(self.display_name)

class Timestamp(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    moment = db.Column(db.DateTime(timezone=True))
    type = db.Column(db.Boolean) # True = Entry | False = Egress

    def __repr__(self):
        descrType = "Entry" if self.type else "Egress"
        return "{} at {}".format(descrType, self.moment)
