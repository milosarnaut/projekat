<?php
    final class Configuration{
        const DATABASE_HOST = 'localhost';
        const DATABASE_USER = 'root';
        const DATABASE_PASS = '';
        const DATABASE_NAME = 'projekat';

	    const SESSION_STORAGE = '\\App\\Core\\Session\\FileSessionStorage';
        const SESSION_STORAGE_DATA = [ './sessions/' ];
        const SESSION_LIFETIME = 3600;

        const FINGERPRINT_PROVIDER_FACTORY = '\\App\\Core\\Fingerprint\\BasicFingerprintProviderFactory';
        const FINGERPRINT_PROVIDER_METHOD  = 'getInstance';
        const FINGERPRINT_PROVIDER_ARGS    = [ 'SERVER' ];

        const MAIL_HOST     = 'smtp.gmail.com';
        const MAIL_PORT     = '587';
        const MAIL_PROTOCOL = 'tls';
        const MAIL_USERNAME = 'somemaile@gmail.com';
        const MAIL_PASSWORD = 'passwordhere';

        const BASE = 'http://localhost/projekat/';
    }
