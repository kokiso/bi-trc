
/*Adiciona a coluna para verificar se o perfil do usuário permite acessar o menu de relatórios*/
alter table USUARIOPERFIL
	add CONSULTAR_RELATORIO varchar(1)
go

alter table BKPUSUARIOPERFIL
	add CONSULTAR_RELATORIO varchar(1)
go

alter view VUSUARIOPERFIL as SELECT UP_CODIGO
  ,UP_NOME
  ,UP_D01,UP_D02,UP_D03,UP_D04,UP_D05,UP_D06,UP_D07,UP_D08,UP_D09,UP_D10
  ,UP_D11,UP_D12,UP_D13,UP_D14,UP_D15,UP_D16,UP_D17,UP_D18,UP_D19,UP_D20
  ,UP_ATIVO,UP_REG,UP_CODUSR, CONSULTAR_RELATORIO
FROM USUARIOPERFIL
go

create table CONSOLIDACAO_TEMPO_INFRACOES
(
	COD_CONSOLIDACAO int identity
		constraint CONSOLIDACAO_TEMPO_INFRACOES_pk
			primary key nonclustered,
	DATA_CONSOLIDACAO datetime not null,
	ULTIMA_POSICAO_MOVIMENTO bigint not null
)
go