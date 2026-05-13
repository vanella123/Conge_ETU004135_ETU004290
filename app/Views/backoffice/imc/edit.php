<?= $this->extend('backoffice/layout') ?>

<?= $this->section('title') ?>Modifier l'IMC<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Modifier Parametre IMC<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Ajustez les intervalles de la classification IMC.<?= $this->endSection() ?>

<?= $this->section('page_actions') ?>
    <a href="<?= base_url('admin/imc') ?>" class="btn btn-secondary">
        <img class="icon" src="<?= esc(base_url('assets/icons/arrow-left.svg')) ?>" alt="">
        Retour
    </a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="card">
        <form action="<?= base_url('admin/imc/update/' . esc($imc['id_imc'])) ?>" method="post" id="imcForm">
        
            <div class="stack">
                <div class="field">
                    <label for="label_imc">Nom (Label)</label>
                    <input type="text" id="label_imc" name="label_imc" value="<?= old('label_imc', esc($imc['label_imc'])) ?>" required>
                </div>
                
                <div class="grid-2">
                    <div class="field">
                        <label for="imc_min">IMC Minimum</label>
                        <input type="number" step="0.01" id="imc_min" name="imc_min" value="<?= old('imc_min', esc($imc['imc_min'])) ?>" required>
                    </div>

                    <div class="field">
                        <label for="imc_max">IMC Maximum</label>
                        <input type="number" step="0.01" id="imc_max" name="imc_max" value="<?= old('imc_max', esc($imc['imc_max'])) ?>" required>
                    </div>
                </div>

                <div id="validationAlert" class="flash error" id="validationAlert">
                    <span id="alertMessage">L'IMC minimum doit etre strictement inferieur a l'IMC maximum.</span>
                </div>

                <div class="field">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <img class="icon" src="<?= esc(base_url('assets/icons/save.svg')) ?>" alt=""> 
                        Enregistrer
                    </button>
                </div>
            </div>

        </form>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        const otherImcs = <?= json_encode($otherImcs ?? []) ?>;

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('imcForm');
            const minInput = document.getElementById('imc_min');
            const maxInput = document.getElementById('imc_max');
            const alertBox = document.getElementById('validationAlert');
            const alertMsg = document.getElementById('alertMessage');
            const submitBtn = document.getElementById('submitBtn');

            function validateValues() {
                const min = parseFloat(minInput.value);
                const max = parseFloat(maxInput.value);
                
                let hasError = false;
                let errorMessage = "";

                if (!isNaN(min) && !isNaN(max)) {
                    if (min >= max) {
                        hasError = true;
                        errorMessage = "L'IMC minimum doit etre strictement inferieur a l'IMC maximum.";
                    } else {
                        // Check overlap
                        for (const other of otherImcs) {
                            const otherMin = parseFloat(other.imc_min);
                            const otherMax = parseFloat(other.imc_max);
                            
                            if (min <= otherMax && max >= otherMin) {
                                hasError = true;
                                errorMessage = `Chevauchement avec ${other.label_imc} [${otherMin} - ${otherMax}].`;
                                break;
                            }
                        }
                    }
                }
                
                if (hasError) {
                    alertBox.style.display = 'block';
                    alertMsg.textContent = errorMessage;
                    minInput.style.borderColor = '#b33a3a';
                    maxInput.style.borderColor = '#b33a3a';
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                } else {
                    alertBox.style.display = 'none';
                    minInput.style.borderColor = '';
                    maxInput.style.borderColor = '';
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                }
                
                return !hasError;
            }

            minInput.addEventListener('input', validateValues);
            maxInput.addEventListener('input', validateValues);
            
            form.addEventListener('submit', (e) => {
                if (!validateValues()) {
                    e.preventDefault();
                }
            });
            
            // Validate on load as well
            validateValues();
        });
    </script>
<?= $this->endSection() ?>
