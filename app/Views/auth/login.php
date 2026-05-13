<?php
// Wrapper view: render the login form partial into the template
$content = view('auth/_login_form', ['errors' => session()->getFlashdata('errors') ?? []]);
echo view('template-conges-rh-ci4', ['content' => $content, 'title' => $title ?? 'Connexion']);
