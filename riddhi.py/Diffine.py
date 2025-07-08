def gcd(a, b):
    while b:
        a, b = b, a % b
    return a

def encrypt_decrypt(msg, e, n):
    return pow(msg, e, n)

p, q = 3, 7
n = p * q
e = 2

phi = (p - 1) * (q - 1)
while gcd(e, phi) != 1:
    e += 1

d = pow(e, -1, phi)

msg = 12.0
print("Message data =", msg)

encrypted_data = encrypt_decrypt(msg, e, n)
print("Encrypted data =", encrypted_data)

original_message = encrypt_decrypt(encrypted_data, d, n)
print("Original Message Sent =", original_message)
