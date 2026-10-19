export class ErroApi extends Error {
    constructor(mensagem, status, erros = {}) {
        super(mensagem);
        this.status = status;
        this.erros = erros;
    }
}

export async function api(caminho, { method = 'GET', body } = {}) {
    let resposta;

    try {
        resposta = await fetch(`/api${caminho}`, {
            method,
            headers: {
                Accept: 'application/json',
                ...(body ? { 'Content-Type': 'application/json' } : {}),
            },
            body: body ? JSON.stringify(body) : undefined,
        });
    } catch {
        throw new ErroApi('Não foi possível conectar ao servidor.', 0);
    }

    if (resposta.status === 204) {
        return null;
    }

    const dados = await resposta.json().catch(() => ({}));

    if (!resposta.ok) {
        throw new ErroApi(dados.message ?? `Erro ${resposta.status} ao falar com a API.`, resposta.status, dados.errors);
    }

    return dados;
}
