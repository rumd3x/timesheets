<?php

if (!file_exists('.env')) {
    copy('.env.example', 'storage/.env') or exit(1);
    symlink('storage/.env', '.env') or exit(2);
}
