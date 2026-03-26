import sys
import hashlib
import uuid
from cryptography.fernet import Fernet

try:
    import bcrypt
except ModuleNotFoundError:
    bcrypt = None

#brcypt fait comme il veut donc j"ai sécu avec hashlib

mode = sys.argv[1]

if mode == "hash":
    mdp = sys.argv[2].encode("utf-8")
    if bcrypt:
        hash_mdp = bcrypt.hashpw(mdp, bcrypt.gensalt()).decode("utf-8")
    else:
        hash_mdp = hashlib.sha256(mdp).hexdigest()
    user_uuid = str(uuid.uuid4())
    print(f"{hash_mdp}|{user_uuid}")

elif mode == "check":
    mdp_tape = sys.argv[2].encode("utf-8")
    hash_bdd = sys.argv[3]
    if bcrypt and hash_bdd.startswith("$2"):
        ok = bcrypt.checkpw(mdp_tape, hash_bdd.encode("utf-8"))
    else:
        ok = hashlib.sha256(mdp_tape).hexdigest() == hash_bdd
    print("Mot de passe correct" if ok else "Mot de passe incorrect")

elif mode == "gen_key":
    print(Fernet.generate_key().decode())

elif mode == "encrypt":
    texte = sys.argv[2].encode()
    clé= sys.argv[3].encode()
    f = Fernet(clé)
    print(f.encrypt(texte).decode())

elif mode == "decrypt":
    message_chiffre = sys.argv[2].encode()
    clé = sys.argv[3].encode()
    f = Fernet(clé)
    print(f.decrypt(message_chiffre).decode())