f=open("f1.txt","w")
print(f)
if f:
    print("file created suessfully")
    f.write("hello world")
    f.close()
