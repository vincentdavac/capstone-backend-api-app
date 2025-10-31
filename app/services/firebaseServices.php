<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FirebaseServices
{
    protected $database;
    public function __construct()
    {
        $firebaseCredentialsPath = base_path(env('FIREBASE_CREDENTIALS'));
        if (!file_exists($firebaseCredentialsPath)) {
            throw new \Exception("Firebase credentials file not found at: $firebaseCredentialsPath");
        }
        $firebaseDatabaseUrl = env('FIREBASE_DATABASE_URL');

        $this->database = (new Factory)
            ->withServiceAccount($firebaseCredentialsPath)
            ->withDatabaseUri($firebaseDatabaseUrl)
            ->createDatabase();
    }
    public function getDatabase()
    {
        return $this->database;
    }

    public function getReference(string $path)
    {
        return $this->database->getReference($path);
    }
}
