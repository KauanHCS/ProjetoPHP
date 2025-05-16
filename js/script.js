document.addEventListener('DOMContentLoaded', function() {
    // Validação de formulário
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = this.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        });
    });
    
    // Mensagem de sucesso após cadastro
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        alert('Banca cadastrada com sucesso!');
    }
    
    // Melhorias de UX para campos de data
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Define a data mínima como hoje
        input.min = new Date().toISOString().split('T')[0];
    });
});

// Adicionar ao evento de submit
form.addEventListener('submit', function(e) {
    // Operador ternário para debug
    const debugMode = (window.location.hostname === 'localhost') ? true : false;
    
    // Validação de data usando operadores de comparação
    const dataInput = this.querySelector('#data');
    const data = new Date(dataInput.value);
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);
    
    if (data < hoje) {
        alert('Data não pode ser no passado!');
        e.preventDefault();
        return;
    }
    
    // Validação criativa usando operador OR (||)
    const tipoTcc = this.querySelector('#tipo_tcc').value;
    if (tipoTcc < 1 || tipoTcc > 5) {
        alert('Selecione um tipo de TCC válido!');
        e.preventDefault();
        return;
    }
    
    if (debugMode) {
        console.log('Formulário validado com sucesso');
    }
});