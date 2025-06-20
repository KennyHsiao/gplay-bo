<?php

namespace App\Providers\Session;

use Illuminate\Session\DatabaseSessionHandler;
use Xn\Admin\Facades\Admin;

class AdminDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data): bool
    {
        $username = Admin::user()->username??"";

        $payload = $this->getDefaultPayload($data);

        $payload['username'] = $username;

        if (! $this->exists) {
            $this->read($sessionId);
        }

        if ($this->exists) {
            $this->performUpdate($sessionId, $payload);
        } else {
            $this->performInsert($sessionId, $payload);
        }

        return $this->exists = true;
    }
}
