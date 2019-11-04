--create/alter a stored proceudre  accordingly
create procedure consolidaInfracao
as
DECLARE @authHeader NVARCHAR(64);
DECLARE @contentType NVARCHAR(64);
DECLARE @postData NVARCHAR(2000);
DECLARE @responseText NVARCHAR(2000);
DECLARE @responseXML NVARCHAR(2000);
DECLARE @ret INT;
DECLARE @status NVARCHAR(32);
DECLARE @statusText NVARCHAR(32);
DECLARE @token INT;
DECLARE @url NVARCHAR(256);
DECLARE @Authorization NVARCHAR(200);

--set your post params
SET @contentType = 'application/x-www-form-urlencoded';
SET @url = '192.168.25.30:1337/api/consolidaInfracao'

-- Open the connection.
EXEC @ret = sp_OACreate 'MSXML2.ServerXMLHTTP', @token OUT;
IF @ret <> 0 RAISERROR('Unable to open HTTP connection.', 10, 1);

-- Send the request.
EXEC @ret = sp_OAMethod @token, 'open', NULL, 'GET', @url, 'false';
--set a custom header Authorization is the header key and VALUE is the value in the header
EXEC sp_OAMethod @token, 'SetRequestHeader', NULL, 'Authorization', 'VALUE'

EXEC @ret = sp_OAMethod @token, 'setRequestHeader', NULL, 'Authentication', NULL;
EXEC @ret = sp_OAMethod @token, 'setRequestHeader', NULL, 'Content-type', @contentType;
EXEC @ret = sp_OAMethod @token, 'send', NULL, NULL;

-- Handle the response.
EXEC @ret = sp_OAGetProperty @token, 'status', @status OUT;
EXEC @ret = sp_OAGetProperty @token, 'statusText', @statusText OUT;
EXEC @ret = sp_OAGetProperty @token, 'responseText', @responseText OUT;

-- Show the response.
PRINT 'Status: ' + @status + ' (' + @statusText + ')';
PRINT 'Response text: ' + @responseText;

-- Close the connection.
EXEC @ret = sp_OADestroy @token;
IF @ret <> 0 RAISERROR('Unable to close HTTP connection.', 10, 1);
go

