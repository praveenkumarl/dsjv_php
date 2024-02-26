CREATE TRIGGER `delete_expiry` AFTER DELETE ON `user`
 FOR EACH ROW BEGIN

DELETE FROM user_trial WHERE imei = OLD.imei;
                     
END	