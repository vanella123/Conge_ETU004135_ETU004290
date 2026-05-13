<?php
namespace App\Controllers;

use App\Models\ImcModel;

class AdminImcController extends BaseController
{
    public function index()
    {
        if (!session()->has('admin_id')) return redirect()->to('/admin/login');

        $imcModel = new ImcModel();
        $imcs = $imcModel->orderBy('imc_min', 'ASC')->findAll();

        return view('backoffice/imc/index', [
            'imcs'      => $imcs,
            'activeNav' => 'imc'
        ]);
    }

    public function edit($id)
    {
        if (!session()->has('admin_id')) return redirect()->to('/admin/login');

        $imcModel = new ImcModel();
        $imc = $imcModel->find($id);

        if (!$imc) {
            session()->setFlashdata('error', 'Parametre IMC introuvable.');
            return redirect()->to('/admin/imc');
        }

        $otherImcs = $imcModel->where('id_imc !=', $id)->findAll();

        return view('backoffice/imc/edit', [
            'imc'       => $imc,
            'otherImcs' => $otherImcs,
            'activeNav' => 'imc'
        ]);
    }

    public function update($id)
    {
        if (!session()->has('admin_id')) return redirect()->to('/admin/login');

        $imcModel = new ImcModel();
        
        $rules = [
            'label_imc' => 'required',
            'imc_min'   => 'required|numeric',
            'imc_max'   => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', 'Veuillez verifier les champs du formulaire.');
            return redirect()->back()->withInput();
        }

        $min = (float) $this->request->getPost('imc_min');
        $max = (float) $this->request->getPost('imc_max');

        if ($min >= $max) {
            session()->setFlashdata('error', 'L\'IMC minimum doit etre strictement inferieur a l\'IMC maximum.');
            return redirect()->back()->withInput();
        }

        $otherImcs = $imcModel->where('id_imc !=', $id)->findAll();
        foreach ($otherImcs as $other) {
            $otherMin = (float) $other['imc_min'];
            $otherMax = (float) $other['imc_max'];

            if ($min <= $otherMax && $max >= $otherMin) {
                session()->setFlashdata('error', sprintf('L\'intervalle [%s - %s] chevauche %s [%s - %s].', $min, $max, $other['label_imc'], $otherMin, $otherMax));
                return redirect()->back()->withInput();
            }
        }

        $ranges = array_merge($otherImcs, [[
            'id_imc' => $id,
            'label_imc' => (string) $this->request->getPost('label_imc'),
            'imc_min' => $min,
            'imc_max' => $max,
        ]]);

        usort($ranges, static fn ($a, $b) => ((float) $a['imc_min']) <=> ((float) $b['imc_min']));

        for ($i = 0; $i < count($ranges) - 1; $i++) {
            $currentMax = (float) $ranges[$i]['imc_max'];
            $nextMin = (float) $ranges[$i + 1]['imc_min'];
            if ($nextMin > $currentMax) {
                session()->setFlashdata('error', 'Un intervalle IMC manque entre ' . $currentMax . ' et ' . $nextMin . '.');
                return redirect()->back()->withInput();
            }
        }

        $imcModel->update($id, [
            'label_imc' => $this->request->getPost('label_imc'),
            'imc_min'   => $min,
            'imc_max'   => $max,
        ]);

        session()->setFlashdata('success', 'Parametre IMC mis a jour avec succes.');
        return redirect()->to('/admin/imc');
    }
}
