CREATE TRIGGER [dbo].[TRGVMOTORISTA_BI] ON [dbo].[VMOTORISTA]
INSTEAD OF INSERT 
AS
BEGIN
   -- CAMPO          |INS|UPD|DEL| TIPO               Obs
   -- ---------------|---|---|---|--------------------|----------------------------------------------------------
   -- MTR_CODIGO     |PK |   |   | INT NN IDENTITY    | Codigo informado
   -- MTR_NOME       |   |   |   | VC(60) NN          | 
   -- MTR_RFID       |   |   |   | VC(30) NN          |
   -- MTR_CODUNI     |   |   |   | INT NN             | Codigo da UNIDADE em FK(UNIDADE)      
   -- MTR_ATIVO      |   |   |   | VC(1) NN check     | S|N     Se o registro pode ser usado em tabelas auxiliares
   -- MTR_REG        |   |   |   | VC(1) NN check     | P|A|S   P=Publico  A=Administrador S=Sistema 
   -- MTR_CODUSR     |   |   |   | INT NN             | Codigo do Usuario em USUARIO que esta tentando INC/ALT/EXC
   -- USR_APELIDO    |   |   |   | VC(15) NN          | Campo relacionado (USUARIO)
   -- ---------------|---|---|---|--------------------|----------------------------------------------------------
   -- O Direito desta tabela em USUARIOPERFEIL(Ver select) 
  SET NOCOUNT ON;  
  DECLARE @direitoNew INTEGER;        -- Recupera o direito de usuario para esta tabela
  DECLARE @fkIntNew INTEGER = 0;      -- Para procurar campo foreign key int
  DECLARE @fkStrNew VARCHAR(20) = ''; -- Para procurar campo foreign key str
  DECLARE @erroNew VARCHAR(70);       -- Buscando retorno de erro para funcao
  -------------------
  -- Campos da tabela
  -------------------
  DECLARE @mtrCodigoNew INTEGER;
  DECLARE @mtrNomeNew VARCHAR(60);
  DECLARE @mtrRfidNew VARCHAR(30);
  DECLARE @mtrCodUniNew INTEGER;
  DECLARE @mtrPosicaoNew BIGINT;
  DECLARE @uniApelidoNew VARCHAR(15);
  DECLARE @mtrAtivoNew VARCHAR(1);
  DECLARE @mtrRegNew VARCHAR(1);
  DECLARE @mtrCodUsrNew INTEGER;
  DECLARE @usrApelidoNew VARCHAR(15);
  DECLARE @usrAdmPubNew VARCHAR(1);
  ---------------------------------------------------
  -- Buscando os campos para checagem antes do insert
  ---------------------------------------------------
  SELECT @mtrCodigoNew   = i.MTR_CODIGO
         ,@mtrNomeNew    = dbo.fncTranslate(i.MTR_NOME,60)
         ,@mtrRfidNew    = UPPER(i.MTR_RFID)
         ,@mtrCodUniNew  = i.MTR_CODUNI
         ,@uniApelidoNew = COALESCE(UNI.UNI_APELIDO,'ERRO')
         ,@mtrPosicaoNew = COALESCE(i.MTR_POSICAO, 0)
         ,@mtrAtivoNew   = UPPER(i.MTR_ATIVO)
         ,@mtrRegNew     = UPPER(i.MTR_REG)
         ,@mtrCodUsrNew  = i.MTR_CODUSR         
         ,@usrApelidoNew = COALESCE(USR.USR_APELIDO,'ERRO')
         ,@usrAdmPubNew  = COALESCE(USR.USR_ADMPUB,'P')         
         ,@direitoNew    = UP.UP_D04
    FROM inserted i
    LEFT OUTER JOIN USUARIO USR ON i.MTR_CODUSR=USR.USR_CODIGO AND USR_ATIVO='S'
    LEFT OUTER JOIN USUARIOPERFIL UP ON USR.USR_CODUP=UP.UP_CODIGO    
    LEFT OUTER JOIN UNIDADE UNI ON i.MTR_CODUNI=UNI.UNI_CODIGO;    
  -----------------------------
  -- VERIFICANDO A FOREIGN KEYs
  -----------------------------
  IF( @usrApelidoNew='ERRO' )
    RAISERROR('NAO LOCALIZADO USUARIO %i PARA ESTE REGISTRO',15,1,@mtrCodUsrNew);
  IF( @uniApelidoNew='ERRO' )
    RAISERROR('NAO LOCALIZADO UNIDADE %i PARA ESTE REGISTRO',15,1,@mtrCodUniNew);
  -------------------------------------------------------------
  -- Checando se o usuario tem direito de cadastro nesta tabela
  -------------------------------------------------------------
  IF( @direitoNew<2 )
    RAISERROR('USUARIO %s NAO POSSUI DIREITO 04 PARA INCLUIR NA TABELA MOTORISTA',15,1,@usrApelidoNew);
  ---------------------------------------------------------------------
  -- Razao social e apelido devem ser grpcos para grades sistema
  ---------------------------------------------------------------------
  --SELECT @fkIntNew=COALESCE(MTR_CODIGO,0) FROM MOTORISTA WHERE MTR_NOME=@mtrNomeNew;
  --IF( @fkIntNew<>0 )
  --  RAISERROR('DESCRITIVO JA CADASTRADO NA TABELA MOTORISTA %i',15,1,@fkIntNew);
  ---------------------------------------------------------------------
  -- Verificando a chave primaria quando nao for identity
  ---------------------------------------------------------------------
  --SELECT @fkStrNew=COALESCE(MTR_NOME,0) FROM MOTORISTA WHERE MTR_CODIGO=@mtrCodigoNew;
  --IF( COALESCE(@fkStrNew,'')<>'' )
  --  RAISERROR('CODIGO JA CADASTRADO NA TABELA MOTORISTA %s',15,1,@fkStrNew);

  ------------------------------
  -- Verificando o campo USR_REG
  ------------------------------
  SET @erroNew=dbo.fncCampoRegInc( @usrAdmPubNew,@mtrRegNew,4 );
  IF( @erroNew != 'OK' )
    RAISERROR(@erroNew,15,1);
  --  
  BEGIN TRY
    INSERT INTO dbo.MOTORISTA( 
      MTR_CODIGO
      ,MTR_NOME
      ,MTR_RFID
      ,MTR_CODUNI
      ,MTR_POSICAO      
      ,MTR_ATIVO
      ,MTR_REG
      ,MTR_CODUSR) VALUES(
      @mtrCodigoNew   -- MTR_CODIGO
      ,REPLACE(@mtrNomeNew, '_', ' ') -- MTR_NOME
      ,@mtrRfidNew    -- MTR_RFID
      ,@mtrCodUniNew  -- MTR_CODUNI
      ,@mtrPosicaoNew -- MTR_POSICAO
      ,@mtrAtivoNew   -- MTR_ATIVO
      ,@mtrRegNew     -- MTR_REG
      ,@mtrCodUsrNew  -- MTR_CODUSR
    );

  ---------------------------------------------------------------------
  -- O mesmo RFID pode ser usado mas nunca dois com ATIVO='S', caso aconteça, inativar o antigo
  ---------------------------------------------------------------------
  IF( @mtrAtivoNew='S' ) BEGIN
    SET @fkIntNew=0;
    SELECT @fkIntNew=COALESCE(MTR_CODIGO,0) FROM MOTORISTA WHERE ((MTR_RFID=@mtrRfidNew) AND (MTR_ATIVO='S'));
      IF( @fkIntNew<>0 )
            UPDATE dbo.MOTORISTA
       SET MTR_ATIVO  = 'N'
    WHERE MTR_CODIGO <> @mtrCodigoNew AND MTR_RFID = @mtrRfidNew AND MTR_ATIVO = 'S';
  END
    ----
    ---------------------------------------------------
    -- Atualizando a qtdade de veiculos em cada unidade
    ---------------------------------------------------
    IF( @mtrAtivoNew='S' AND @fkIntNew<>0 )
      UPDATE UNIDADE SET UNI_QTOSMTR=(UNI_QTOSMTR+1) WHERE UNI_CODIGO=@mtrCodUniNew;
    ---------------
    -- Gravando LOG
    ---------------
    INSERT INTO dbo.BKPMOTORISTA(
      MTR_ACAO
      ,MTR_CODIGO
      ,MTR_NOME
      ,MTR_RFID
      ,MTR_CODUNI
      ,MTR_ATIVO
      ,MTR_REG
      ,MTR_CODUSR) VALUES(
      'I'                         -- MTR_ACAO
      ,@mtrCodigoNew              -- MTR_CODIGO
      ,@mtrNomeNew                -- MTR_NOME
      ,@mtrRfidNew                -- MTR_RFID
      ,@mtrCodUniNew              -- MTR_CODUNI
      ,@mtrAtivoNew               -- MTR_ATIVO
      ,@mtrRegNew                 -- MTR_REG
      ,@mtrCodUsrNew              -- MTR_CODUSR
    );
  END TRY
  BEGIN CATCH
    DECLARE @ErrorMessage NVARCHAR(4000);
    DECLARE @ErrorCode INT;
    DECLARE @ErrorSeverity INT;
    DECLARE @ErrorState INT;
    SELECT @ErrorMessage=ERROR_MESSAGE(),@ErrorSeverity=ERROR_SEVERITY(),@ErrorState=ERROR_STATE(), @ErrorCode=ERROR_NUMBER();
    IF @ErrorCode= 1205 -- um deadlock foi detectado
    SET @ErrorCode = N'Falha ao executar a operação. Tente novamente mais tarde.'
    RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
    RETURN;
  END CATCH
END
go

