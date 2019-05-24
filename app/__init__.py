from flask import Flask
from flask_sqlalchemy import SQLAlchemy
from flask_login import LoginManager
from flask_migrate import Migrate
from flask_bootstrap import Bootstrap

db = SQLAlchemy()
login_manager = LoginManager()

def create_app():
    app = Flask(__name__, instance_relative_config=True)
    app.config.from_pyfile('../config.py')

    Bootstrap(app)
    db.init_app(app)
    login_manager.init_app(app)
    login_manager.login_message = "You must be logged in to access this page."
    login_manager.login_view = "auth.login"

    migrate = Migrate(app, db)
    from app import models

    from .admin import admin as admin_blueprint
    app.register_blueprint(admin_blueprint)

    from .auth import auth as auth_blueprint
    app.register_blueprint(auth_blueprint)

    return app
