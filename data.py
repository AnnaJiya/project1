'''score=[20,30,40,35,34]
high=score[0]
low=score[0]
for i in score:
    if(high>i):
        high=i
    if(low<i):
        low=i
print(high)
print(low)
'''

'''
rows=10
for i in range(1,rows+1,1):
    for j in range(1,rows+1,1):
        print(j*i,end="   ")
    print()
'''
rows=5
for i in range(1,rows+1):
    for j in range(i+1,1):
        print("*",end="   ")
    print()
    
