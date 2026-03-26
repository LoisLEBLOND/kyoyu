import sys
import hashlib

try:
    import bcrypt
except ModuleNotFoundError:
    bcrypt = None

mode = sys.argv[1]

if mode == 'hash':
    mdp = sys.argv[2].encode('utf-8')
    if bcrypt:
        hash_mdp = bcrypt.hashpw(mdp, bcrypt.gensalt()).decode('utf-8')
    else:
        hash_mdp = hashlib.sha256(mdp).hexdigest()
    print(hash_mdp)

elif mode == 'check':
    mdp_tape = sys.argv[2].encode('utf-8')
    hash_bdd = sys.argv[3]
    if bcrypt and hash_bdd.startswith('$2'):
        ok = bcrypt.checkpw(mdp_tape, hash_bdd.encode('utf-8'))
    else:
        ok = hashlib.sha256(mdp_tape).hexdigest() == hash_bdd
    print('Mot de passe correct' if ok else 'Mot de passe incorrect')

else:
    print('Erreur: mode inconnu')
    sys.exit(1)