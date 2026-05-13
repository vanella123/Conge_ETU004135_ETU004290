<?php
$content = view('rh/demandes/_index_content', [
	'demandes'    => $demandes ?? [],
	'stats'        => $stats ?? [],
	'departments'  => $departments ?? [],
	'filters'      => $filters ?? [],
	'annees'       => $annees ?? [],
]);

echo view('template-conges-rh-ci4', ['content' => $content, 'title' => $title ?? 'Demandes RH']);
