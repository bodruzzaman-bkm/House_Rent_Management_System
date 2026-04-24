from flask import Flask, render_template, request, flash
import mysql.connector
from mysql.connector import Error

app = Flask(__name__)
app.secret_key = 'secret123'

conn = mysql.connector.connect(host='localhost', user='root', password='', database='house_rent')
cursor = conn.cursor(dictionary=True)

@app.route('/')
def home():
    return render_template('generate_bill.html')

@app.route('/generate_bill', methods=['GET', 'POST'])
def generate_bill():
    cursor.execute("""
        SELECT l.agreement_id, f.flat_id, f.asking_rent
        FROM links l JOIN flat f ON l.flat_id=f.flat_id
        WHERE f.status='Rented'
    """)
    agreements = cursor.fetchall()

    if request.method == 'POST':
        agreement_id = request.form['agreement_id']
        billing_month = request.form['billing_month']
        electricity = float(request.form['electricity'])
        gas = float(request.form['gas'])
        maintainance = float(request.form['maintainance'])

        cursor.execute("""
            SELECT f.asking_rent FROM links l
            JOIN flat f ON l.flat_id=f.flat_id
            WHERE l.agreement_id=%s
        """, (agreement_id,))
        rent = float(cursor.fetchone()['asking_rent'])

        total = rent + electricity + gas + maintainance

        cursor.execute("SELECT * FROM monthly_bill WHERE agreement_id=%s AND billing_month=%s", (agreement_id, billing_month))
        if cursor.fetchone():
            flash('Bill already exists for this month.', 'danger')
        else:
            cursor.execute("""
                INSERT INTO monthly_bill
                (agreement_id,billing_month,base_rent,maintainance,electricity,gas,payment_status)
                VALUES (%s,%s,%s,%s,%s,%s,'Unpaid')
            """, (agreement_id,billing_month,rent,maintainance,electricity,gas))
            conn.commit()
            return render_template('success.html', total=total, month=billing_month)

    return render_template('generate_bill.html', agreements=agreements)

if __name__ == '__main__':
    app.run(debug=True)