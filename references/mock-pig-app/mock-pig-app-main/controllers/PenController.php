<?php

require_once __DIR__ . '/../models/Pig.php';
require_once __DIR__ . '/../models/Pen.php';

class PenController
{
    public function show(string $penName): void
    {
        $penName = urldecode($penName);
        $pigs = Pig::byPen($penName);

        require __DIR__ . '/../views/pens/show.php';
    }

    public function create(): void
    {
        $errors = [];
        $pen = ['name' => ''];

        require __DIR__ . '/../views/pens/create.php';
    }

    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $errors = [];

        if ($name === '') {
            $errors[] = 'Pen name is required.';
        }

        if (!empty($errors)) {
            $pen = ['name' => $name];
            require __DIR__ . '/../views/pens/create.php';
            return;
        }

        Pen::create($name);

        header('Location: /pigs/create?pen=' . rawurlencode($name));
        exit;
    }

    public function delete(): void
    {
        $name = trim($_POST['name'] ?? '');

        if ($name !== '') {
            Pen::delete($name);
        }

        header('Location: /dashboard');
        exit;
    }
}
