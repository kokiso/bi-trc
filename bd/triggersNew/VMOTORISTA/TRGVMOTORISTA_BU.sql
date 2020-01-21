CREATE TRIGGER [dbo].[TRGVMOTORISTA_BU] ON [dbo].[VMOTORISTA]
INSTEAD OF UPDATE
AS
BEGIN
  SET NOCOUNT ON;  
  DECLARE @direitoNew INTEGER;       -- Recupera o direito de usuario para esta tabela
  DECLARE @fkIntNew INTEGER    = 0;  -- Para procurar campo foreign key int
  DECLARE @fkStrNew VARCHAR(3) = ''; -- Para procurar campo foreign key str
  DECLARE @erroNew VARCHAR(70);      -- Buscando retorno de erro para funcao
  -----------------------
  -- Campos NEW da tabela
  -----------------------
  DECLARE @mtrCodigoNew INTEGER;
  DECLARE @mtrNomeNew VARCHAR(60);
  DECLARE @mtrRfidNew VARCHAR(30);
  DECLARE @mtrCodUniNew INTEGER;
  DECLARE @uniApelidoNew VARCHAR(15);
  DECLARE @mtrAtivoNew VARCHAR(1);
  DECLARE @mtrRegNew VARCHAR(1);
  DECLARE @mtrCodUsrNew INTEGER;
  DECLARE @mtrVeiculoNew VARCHAR(10);
  DECLARE @usrApelidoNew VARCHAR(15);
  DECLARE @usrAdmPubNew VARCHAR(1);
  -------------------------------------------------------
  -- Buscando os campos NEW para checagem antes do insert
  -------------------------------------------------------
  SELECT @mtrCodigoNew   = i.MTR_CODIGO
         ,@mtrNomeNew    = UPPER(i.MTR_NOME)
         ,@mtrRfidNew    = NULLIF(UPPER(i.MTR_RFID), '')
         ,@mtrCodUniNew  = i.MTR_CODUNI
         ,@uniApelidoNew = COALESCE(UNI.UNI_APELIDO,'ERRO')
         ,@mtrAtivoNew   = UPPER(i.MTR_ATIVO)
         ,@mtrRegNew     = UPPER(i.MTR_REG)
         ,@mtrCodUsrNew  = i.MTR_CODUSR         
         ,@mtrVeiculoNew = i.MTR_VEICULO
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
  IF( @direitoNew<3 )
    RAISERROR('USUARIO %s NAO POSSUI DIREITO 04 PARA INCLUIR NA TABELA MOTORISTA',15,1,@usrApelidoNew);
  --
  --
  ------------------------------------------------------------------------------------
  -- Se checar até aqui verifico os campos que estão no banco de dados antes de gravar  
  -- Campos OLD da tabela
  ------------------------------------------------------------------------------------
  DECLARE @mtrCodigoOld INTEGER;
  DECLARE @mtrNomeOld VARCHAR(60);
  DECLARE @mtrRfidOld VARCHAR(30);
  DECLARE @mtrCodUniOld INTEGER;
  DECLARE @mtrAtivoOld VARCHAR(1);
  DECLARE @mtrVeiculoOld VARCHAR(10);
  DECLARE @mtrRegOld VARCHAR(1);
  DECLARE @mtrCodUsrOld INTEGER;
  SELECT @mtrCodigoOld   = o.MTR_CODIGO
         ,@mtrNomeOld    = o.MTR_NOME
         ,@mtrRfidOld    = o.MTR_RFID
         ,@mtrCodUniOld  = o.MTR_CODUNI
         ,@mtrAtivoOld   = o.MTR_ATIVO
         ,@mtrRegOld     = o.MTR_REG
         ,@mtrVeiculoOld = o.MTR_VEICULO
         ,@mtrCodUsrOld  = o.MTR_CODUSR         
    FROM MOTORISTA o WHERE o.MTR_CODIGO=@mtrCodigoNew;  
  ---------------------------------------------------------------------
  -- Primary Key nao pode ser CREATEada
  ---------------------------------------------------------------------
  IF( @mtrCodigoOld<>@mtrCodigoNew )
    RAISERROR('CAMPO CODIGO NAO PODE SER CREATEADO',15,1);  
  ---------------------------------------------------------------------
  -- Descritivo nao pode ser duplicado
  ---------------------------------------------------------------------
  --IF( @mtrNomeOld<>@mtrNomeNew ) BEGIN
  --  SELECT @fkStrNew=COALESCE(MTR_CODIGO,'') FROM MOTORISTA WHERE MTR_NOME=@mtrNomeNew;
  --  IF( @fkStrNew<>'' )
  --    RAISERROR('DESCRITIVO JA CADASTRADO NA TABELA MOTORISTA %s',15,1,@fkStrNew);
  --END

  ------------------------------
  -- Verificando o campo USR_REG
  ------------------------------
  IF( @mtrRegOld<>@mtrRegNew ) BEGIN
    SET @erroNew=dbo.fncCampoRegAlt( @usrAdmPubNew,@mtrRegOld,@mtrRegNew,4 );
    IF( @erroNew <> 'OK' )
      RAISERROR(@erroNew,15,1);
  END    
  --  
  BEGIN TRY
    UPDATE dbo.MOTORISTA
       SET MTR_NOME   = @mtrNomeNew
          ,MTR_RFID   = @mtrRfidNew
          ,MTR_CODUNI = @mtrCodUniNew
          ,MTR_ATIVO  = @mtrAtivoNew
          ,MTR_REG    = @mtrRegNew
          ,MTR_CODUSR = @mtrCodUsrNew
          ,MTR_VEICULO= @mtrVeiculoNew
    WHERE MTR_CODIGO  = @mtrCodigoNew;

  ---------------------------------------------------------------------
  -- Ao reativar um motorista, se já houver outro com o mesmo RFID, não permitir
  ---------------------------------------------------------------------
    IF( @mtrAtivoNew='S' AND @mtrRfidNew IS NOT NULL ) BEGIN
      SELECT @fkIntNew=COALESCE(MTR_CODIGO,0) FROM MOTORISTA WHERE MTR_CODIGO <> @mtrCodigoNew AND MTR_RFID=@mtrRfidNew AND MTR_ATIVO='S';
      IF( @fkIntNew<>0 )
        RAISERROR('RFID JA CADASTRADO NA TABELA MOTORISTA NO CODIGO %i',15,1,@fkIntNew);
    END
  ---------------------------------------------------------------------
  -- O mesmo RFID pode ser usado mas nunca dois com ATIVO='S', caso aconteça, inativar o antigo
  ---------------------------------------------------------------------
  IF( @mtrRfidOld<>@mtrRfidNew ) BEGIN
    IF( @mtrAtivoNew='S' ) BEGIN
      SELECT @fkIntNew=COALESCE(MTR_CODIGO,0) FROM MOTORISTA WHERE MTR_RFID=@mtrRfidNew AND MTR_ATIVO='S';
      IF( @fkIntNew<>0 )
            UPDATE dbo.MOTORISTA
       SET MTR_ATIVO  = 'N'
    WHERE MTR_CODIGO <> @mtrCodigoNew AND MTR_RFID = @mtrRfidNew AND MTR_ATIVO = 'S';
    END
  END
    ---------------------------------------------------
    -- Atualizando a qtdade de veiculos em cada unidade
    ---------------------------------------------------
    IF( @mtrAtivoOld<>@mtrAtivoNew ) BEGIN
      IF( @mtrAtivoNew='S' ) BEGIN
        UPDATE UNIDADE SET UNI_QTOSMTR=(UNI_QTOSMTR+1) WHERE UNI_CODIGO=@mtrCodUniNew;
      END ELSE BEGIN  
        UPDATE UNIDADE SET UNI_QTOSMTR=(UNI_QTOSMTR-1) WHERE UNI_CODIGO=@mtrCodUniNew;
      END  
    END;  
    ---------------
    -- Gravando LOG
    ---------------
    IF( (@mtrNomeOld<>@mtrNomeNew) OR (@mtrRfidOld<>@mtrRfidNew) OR (@mtrCodUniOld<>@mtrCodUniNew) OR (@mtrAtivoOld<>@mtrAtivoNew) OR (@mtrRegOld<>@mtrRegNew) ) BEGIN
      INSERT INTO dbo.BKPMOTORISTA(
        MTR_ACAO
        ,MTR_CODIGO
        ,MTR_NOME
        ,MTR_RFID
        ,MTR_CODUNI
        ,MTR_ATIVO
        ,MTR_REG
        ,MTR_VEICULO
        ,MTR_CODUSR) VALUES(
        'A'                       -- MTR_ACAO
        ,@mtrCodigoNew            -- MTR_CODIGO
        ,@mtrNomeNew              -- MTR_NOME
        ,@mtrRfidNew              -- MTR_RFID
        ,@mtrCodUniNew            -- MTR_CODUNI
        ,@mtrAtivoNew             -- MTR_ATIVO
        ,@mtrRegNew               -- MTR_REG
        ,@mtrVeiculoNew           -- MTR_VEICULO
        ,@mtrCodUsrNew            -- MTR_CODUSR
      );
    END
  END TRY
  BEGIN CATCH
    DECLARE @ErrorMessage NVARCHAR(4000);
    DECLARE @ErrorSeverity INT;
    DECLARE @ErrorState INT;
    SELECT @ErrorMessage=ERROR_MESSAGE(),@ErrorSeverity=ERROR_SEVERITY(),@ErrorState=ERROR_STATE();
    RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
    RETURN;
  END CATCH
END
go

