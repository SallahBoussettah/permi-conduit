import csv

data = [
    ["Dans le transport, les accidents avec blessures du chauffeur sont plus nombreux en circulation que hors circulation. Vrai ou Faux ?", "yes_no", "Vrai", "Faux", "", "", "2"],
    ["Quelle est la catégorie du permis exigée pour conduire un véhicule de transport en commun comportant 45 places circulant à vide ?", "multiple_choice", "Permis B", "Permis C1", "Permis D", "Permis C", "3"],
    ["Pendant une pause, êtes-vous autorisé à ranger le chargement du véhicule ?", "yes_no", "Oui", "Non", "", "", "2"],
    ["Ce signal interdit-il l'accès aux véhicules mesurant plus de 3,50 m de hauteur, chargement compris ?", "yes_no", "Oui", "Non", "", "", "1"],
    ["Comment appelle-t-on les transports exécutés à titre onéreux pour le compte d'un client ?", "multiple_choice", "Transport privé", "Transport pour compte propre", "Transport pour compte d'autrui", "Transport bénévole", "3"],
    ["En règle générale, de quelle longueur maximale un chargement muni d'une signalisation réglementaire peut-il dépasser l'arrière du véhicule ?", "multiple_choice", "1 m", "2 m", "3 m", "Sans limite si bien signalé", "3"],
    ["Vous conduisez un tracteur solo de 19 t de PTAC et 38 t de PTRA. A quelle vitesse êtes-vous limité sur route à sens unique prioritaire ?", "multiple_choice", "70 km/h", "80 km/h", "90 km/h", "100 km/h", "2"],
    ["Parmi les types de graissage moteur, il existe le graissage par pression. Vrai ou faux ?", "yes_no", "Vrai", "Faux", "", "", "1"],
    ["La rémunération mensuelle d'un conducteur salarié peut être calculée en fonction de la charge transportée pendant le mois. Vrai ou Faux ?", "yes_no", "Vrai", "Faux", "", "", "2"],
    ["En cas de panne du chronotachygraphe, le délai maximal de remise en état, à compter de la panne, est fixé à ... ?", "multiple_choice", "24 heures", "48 heures", "7 jours", "1 mois", "3"]
]

header = ["question_text", "question_type", "answer_1", "answer_2", "answer_3", "answer_4", "correct_answer"]

filename = "questions.csv"

try:
    with open(filename, 'w', newline='', encoding='utf-8') as csvfile:
        csvwriter = csv.writer(csvfile)

        csvwriter.writerow(header)

        csvwriter.writerows(data)
    print(f"Successfully created '{filename}'")
except IOError:
    print(f"Error: Could not write to the file '{filename}'. Please check permissions or disk space.")
except Exception as e:
    print(f"An unexpected error occurred: {e}")

