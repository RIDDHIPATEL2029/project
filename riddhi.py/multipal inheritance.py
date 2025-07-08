class base:
    def add(self,a,b):
        return(a+b)
    
    def sub(self,a,b):
        return(a-b)
class base2(base):
    def mul(self,a,b):
        return(a*b)
class base3(base2):
    def div(self,a,b):
        return(a/b)
    
o=base3()
print(o.add(4,5))
print(o.sub(4,5))
print(o.mul(4,5))
print(o.div(4,5))
