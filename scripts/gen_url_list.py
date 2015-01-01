#!/usr/bin/env python
"""
Script to generate a url list that can be fed into google's sitemap gen

Usage: python gen_url_list.py
"""

import sqlite3

conn = sqlite3.connect('../cache/tag_db.sqlite')
conn.row_factory = sqlite3.Row
c = conn.cursor()
f = open('url_list.txt','w')
for row in c.execute('SELECT * FROM tags ORDER BY genre_folder, artist, title ASC'):
    f.write('http://jpv.everythingisawesome.us/song/?genre={}&song={}\n'.format(row['genre_folder'], row['href']))
f.close()
conn.close()
