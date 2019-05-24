import sys

from app import create_app
from app import db
from app.models import User

print("Running init script...")

app = create_app()
with app.app_context():
    user = db.session.query(User).get(1)

    if (user != None):
        print("User default already created previously. Exiting.")
        print(user)
        sys.exit(0)

    print("Creating default user...")
    user = User(email="admin@admin.com", password="changethis", display_name="Administrator")
    db.session.add(user)
    db.session.commit()
    print("Default User \"{}\" created! Email: {} API Key: {}".format(user.display_name, user.email, user.api_key))
