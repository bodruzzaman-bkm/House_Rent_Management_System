@app.route('/request/<int:id>')
def request_flat(id):
    tenant_id = session['user_id']

    cursor.execute("SELECT owner_id FROM flats WHERE flat_id=%s", (id,))
    owner = cursor.fetchone()

    query = "INSERT INTO flat_requests (flat_id, tenant_id, owner_id) VALUES (%s,%s,%s)"
    cursor.execute(query, (id, tenant_id, owner['owner_id']))
    db.commit()

    return redirect('/tenant_dashboard')
