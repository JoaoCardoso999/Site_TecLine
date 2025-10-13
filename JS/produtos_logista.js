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

// Funcao para listar marcas em tabelas
function listarMarcas(nometabelamarcas) {
// Espera o HTML carregar para só buscar a listar de marcas
document.addEventListener('DOMContentLoaded',()=>{
    // Captura o local onde sera listado os dados no html
const tbody = document.getElementById(nometabelamarcas);
//endpoint que devolve json
const url = "../php/cadastro_marcas.php?listar=1";

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

// Configurando a tabela com os dados
 const row = m => `
    <tr>
      <td>
        <img
          src="${m.imagem ? `data:${m.mime||'image/jpeg'};base64,${m.imagem}`
          : ph(m.nome)}"
          alt="${esc(m.nome||'Marca')}"
          style="width:60px;height:60px;object-fit:cover;border-radius:8px">
      </td>
      <td>${esc(m.nome||'-')}</td>
      <td class="text-end">
        <button class="btn btn-sm btn-warning" data-id="${m.idMarcas}">Editar</button>
        <button class="btn btn-sm btn-danger"  data-id="${m.idMarcas}">Excluir</button>
      </td>
    </tr>`;

    // Fazer a requisição ao php e preencher a tabela
    fetch(url,{cache: 'no-store'})
    // converter o json e redenriza
    .then(r => r.json())
    // trata o json e renderiza
    .then(d =>{
        if(!d.ok)throw new Error(d.Error || "Erro ao listar");
    // Se houver marcas, monta as linhas; senao, mostra uma mensagem
    tbody.innerHTML = d.marcas?.lenght
    ? d.marcas.map(row).join('')
    :`<tr><td colspan="3">Nenhuma marca cadastrada.</td></tr>`;

    })

    // Qualquer erro ele executa este codigo
.catch(err =>{
    tbody.innerHTML= `<str><td colspan="3">Falha ao carregar:
    ${esc(err.message)}</td></tr>`;

})


});

}



listarMarcas("#tabelaMarcas");
listarcategorias("#pCategorias");
listarcategorias("#prodcat");


