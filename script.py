import sys
import bcrypt
import uuid
from cryptography.fernet import Fernet

if sys.argv[1] == "hash":
    mdp = sys.argv[2].encode('utf-8')
    hash_mdp = bcrypt.hashpw(mdp, bcrypt.gensalt())
    print(hash_mdp.decode('utf-8'))

elif sys.argv[1] == "check":
    mdp_tape = sys.argv[2].encode('utf-8')
    hash_bdd = sys.argv[3].encode('utf-8')
    if bcrypt.checkpw(mdp_tape, hash_bdd):
        print("Mot de passe correct")
    else:
        print("Mot de passe incorrect")