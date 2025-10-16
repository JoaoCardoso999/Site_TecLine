function listarcategorias(nomeid){
(async () => {
    // selecionando o elemento html da tela de cadastro de produtos
    const sel = document.querySelector(nomeid);
    try {
        // criando a váriavel que guardar os dados vindo do php, que estão no metodo de listar
        const r = await fetch("../PHP/cadastro_categorias.php?listar=1");
        // se o retorno do php vier false, significa que não foi possivel listar os dados
        if (!r.ok) throw new Error("Falha ao listar categorias!");
        /* se vier dados do php, ele joga as 
        informações dentro do campo html em formato de texto
        innerHTML- inserir dados em elementos html
        */
        sel.innerHTML = await r.text();
    } catch (e) {
        // se dê erro na listagem, aparece Erro ao carregar dentro do campo html
        sel.innerHTML = "<option disable>Erro ao carregar</option>"
    }
})();
}

async function listMarcas(nomeid) {
  const sel = document.querySelector(nomeid);
  try {
    // Faz a requisição para o PHP (já retorna JSON)
    const r = await fetch("../PHP/cadastro_marca.php?listar=1");
    if (!r.ok) throw new Error("Falha ao listar marcas!");

    // Converte o retorno para JSON
    const data = await r.json();

    // Verifica se a resposta contém o array de marcas
    if (!data.ok || !Array.isArray(data.marcas)) {
      throw new Error("Formato de resposta inválido");
    }

    // Limpa o select e adiciona o item inicial
    sel.innerHTML = '';

    // Percorre as marcas e adiciona apenas o nome em cada option
    data.marcas.forEach(marca => {
      const opt = document.createElement("option");
      opt.value = marca.idMarcas;     // valor do option = ID da marca
      opt.textContent = marca.nome;   // texto visível = nome
      sel.appendChild(opt);
    });

  } catch (e) {
    console.error(e);
    sel.innerHTML = "<option disabled>Erro ao carregar marcas</option>";
  }
}

// função de listar formas de pagamento em tabela
function listarFormasPagamento(tabelaPG) {
  // Aguarda o carregamento completo do DOM antes de executar
  document.addEventListener('DOMContentLoaded', () => {
    // Obtém o elemento <tbody> onde as linhas serão inseridas
    const tbody = document.getElementById(tabelaPG);
    // URL da requisição que retorna os dados em formato JSON
    const url   = '../PHP/cadastro_formas_pagamento.php?listar=1&format=json';

    // Função para escapar caracteres especiais e evitar injeção de HTML
    const esc = s => (s||'').replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));

    // Função que monta uma linha (<tr>) da tabela para cada forma de pagamento
    const row = f => `
      <tr>
        <td>${Number(f.id) || ''}</td>
        <td>${esc(f.nome || '-')}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-warning" data-id="${f.id}">Editar</button>
          <button class="btn btn-sm btn-danger"  data-id="${f.id}">Excluir</button>
        </td>
      </tr>`;

    // Faz a requisição dos dados e preenche a tabela
    fetch(url, { cache: 'no-store' })
      .then(r => r.json()) // Converte a resposta para JSON
      .then(d => {
        // Verifica se a resposta é válida
        if (!d.ok) throw new Error(d.error || 'Erro ao listar formas de pagamento');
        // Extrai o array de formas de pagamento (pode ter nomes diferentes no JSON)
        const arr = d.formas_pagamento || d.formas || [];
        // Insere as linhas na tabela ou mostra mensagem se não houver dados
        tbody.innerHTML = arr.length
          ? arr.map(row).join('')
          : `<tr><td colspan="3">Nenhuma forma de pagamento cadastrada.</td></tr>`;
      })
      .catch(err => {
        // Exibe mensagem de erro caso a requisição falhe
        tbody.innerHTML = `<tr><td colspan="3">Falha ao carregar: ${esc(err.message)}</td></tr>`;
      });
  });
}


// função de listar fretes em tabela
function listarFretes(tabelaFt) {
  // Aguarda o carregamento completo do DOM antes de executar
  document.addEventListener('DOMContentLoaded', () => {
    // <tbody> onde as linhas serão inseridas
    const tbody = document.getElementById(tabelaFt);
    // URL da requisição que retorna os fretes em formato JSON
    const url   = '../PHP/cadastro_frete.php?listar=1&format=json';

    // Função para escapar caracteres especiais (evita injeção de HTML)
    const esc = s => (s||'').replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));

    // Cria um formatador de moeda para exibir valores em reais
    const moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    // Função que monta cada linha (<tr>) da tabela com os dados do frete
    const row = f => `
      <tr>
        <td>${Number(f.id) || ''}</td>
        <td>${esc(f.bairro || '-')}</td>
        <td>${esc(f.transportadora || '-')}</td>
        <td class="text-end">${moeda.format(parseFloat(f.valor ?? 0))}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-warning" data-id="${f.id}">
            <i class="bi bi-pencil"></i> Editar
          </button>
          <button class="btn btn-sm btn-danger" data-id="${f.id}">
            <i class="bi bi-trash"></i> Excluir
          </button>
        </td>
      </tr>`;

    // Faz a requisição e preenche a tabela com os dados dos fretes
    fetch(url, { cache: 'no-store' })
      .then(r => r.json()) // Converte a resposta para JSON
      .then(d => {
        // Verifica se o retorno está OK
        if (!d.ok) throw new Error(d.error || 'Erro ao listar fretes');
        // Extrai o array de fretes
        const fretes = d.fretes || [];
        // Preenche a tabela ou mostra mensagem se estiver vazia
        tbody.innerHTML = fretes.length
          ? fretes.map(row).join('')
          : `<tr><td colspan="5" class="text-center text-muted">Nenhum frete cadastrado.</td></tr>`;
      })
      .catch(err => {
        // Exibe mensagem de erro em caso de falha na requisição
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
      });
  });
}


// função de listar marcas em tabelas
function listarMarcas(nometabelamarcas){
// Espera o HTML carregar para só então buscar e preencher a tabela
document.addEventListener('DOMContentLoaded', () => {
  // <tbody> onde as linhas serão inseridas
  const tbody = document.getElementById('tabelaMarcas');

  // Endpoint que devolve JSON { ok, count, marcas[] }
  const url = '../PHP/cadastro_marca.php?listar=1';

  // --- util 1) esc(): escapa caracteres especiais no texto (evita quebrar o HTML)
  const esc = s => (s||'').replace(/[&<>"']/g, c => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[c]));

  // --- util 2) ph(): gera um SVG base64 com as iniciais, usado quando não há imagem
  const ph  = n => 'data:image/svg+xml;base64,' + btoa(
    `<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60">
       <rect width="100%" height="100%" fill="#eee"/>
       <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
             font-family="sans-serif" font-size="12" fill="#999">
         ${(n||'?').slice(0,2).toUpperCase()}
       </text>
     </svg>`
  );

  // --- util 3) row(): recebe 1 marca e retorna o HTML <tr> correspondente
  // Usa a imagem em base64 se existir; senão usa o placeholder SVG
  const row = m => `
    <tr>
      <td>
        <img
          src="${m.imagem ? `data:${m.mime||'image/jpeg'};base64,${m.imagem}` : ph(m.nome)}"
          alt="${esc(m.nome||'Marca')}"
          style="width:60px;height:60px;object-fit:cover;border-radius:8px">
      </td>
      <td>${esc(m.nome||'-')}</td>
      <td class="text-end">
        <button class="btn btn-sm btn-warning" data-id="${m.idMarcas}">Editar</button>
        <button class="btn btn-sm btn-danger"  data-id="${m.idMarcas}">Excluir</button>
      </td>
    </tr>`;

  // Faz a requisição ao PHP (sem cache) e preenche a tabela
  fetch(url, { cache: 'no-store' })
    // Converte a resposta em JSON
    .then(r => r.json())
    // Trata o JSON e renderiza
    .then(d => {
      // Se o backend sinalizou erro, lança para o .catch
      if (!d.ok) throw new Error(d.error || 'Erro ao listar');

      // Se houver marcas, monta as linhas; senão, mostra mensagem de vazio
      tbody.innerHTML = d.marcas?.length
        ? d.marcas.map(row).join('')            // junta todas as <tr> num único HTML
        : `<tr><td colspan="3">Nenhuma marca cadastrada.</td></tr>`;
    })
    // Qualquer erro (rede, JSON inválido, etc.) cai aqui
    .catch(err => {
      tbody.innerHTML = `<tr><td colspan="3">Falha ao carregar: ${esc(err.message)}</td></tr>`;
    });
});
}


// Chama as funções para listar os dados nas tabelas correspondentes
listarFormasPagamento("tbPagamentos");
listarFretes("tbFretes");
listMarcas("#pMarca");
listarMarcas("#tabelaMarcas");
listarcategorias("#pCat");
listarcategorias("#prodcat");


