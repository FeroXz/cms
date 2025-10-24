<?php
const APP_NAME = 'FeroxZ CMS';
const DATA_PATH = __DIR__ . '/../storage/database.sqlite';
const UPLOAD_PATH = __DIR__ . '/../public/uploads';
const IMPORT_QUEUE_PATH = __DIR__ . '/../storage/import_queue';
const BASE_URL = '';

const PASSWORD_ALGO = PASSWORD_DEFAULT;

const MEDIA_ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const MEDIA_MAX_FILESIZE_BYTES = 20 * 1024 * 1024; // 20 MB
const MEDIA_THUMB_MAX_WIDTH = 480;
const MEDIA_MEDIUM_MAX_WIDTH = 1200;
